<?php

namespace App\Listeners\Applications;

use App\Events\Applications\LogActivity;
use App\Services\Applications\Gateway\MachineTokenManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendLogToLoggingService implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected ?MachineTokenManager $tokens = null) {}

    public function handle(LogActivity $event): void
    {
        $payload = $event->payload->toArray();

        try {
            if (app()->bound('request')) {
                $req = request();
                $payload['ip'] = $payload['ip'] ?? $req->ip();
                $payload['user_agent'] = $payload['user_agent'] ?? $req->userAgent();
                $payload['request_id'] = $payload['request_id'] ?? $req->header(config('gateway.request_id_header', 'X-Request-ID'));
                $payload['trace_id'] = $payload['trace_id'] ?? $req->header('X-Trace-ID');
            }
        } catch (\Throwable $e) {
            // ignore enrichment errors
        }

        $payload['service'] = $payload['service'] ?? config('app.name');
        $payload['env'] = $payload['env'] ?? app()->environment();

        // 1) Which base URI to call (from your gateway.php)
        $base = (string) data_get(config('gateway.services'), 'log_service.base_uri');
        abort_unless($base, 500, 'log_service.base_uri not configured');
        $endpoint = rtrim($base, '/').'/v1/system-logs';

        // 2) Get a machine token (audience = token_service from your gateway.php)
        $aud = (string) data_get(config('gateway.services'), 'log_service.token_service', 'log-service');
        $token = $this->tokens->get($aud); // scope optional; omit if unused

        try {

            $http = Http::timeout((float) config('gateway.cb.timeout', 3.0))
                ->retry((int) config('gateway.cb.retries', 1), (int) config('gateway.cb.retry_delay', 150))
                ->withToken($token)
                ->withHeaders([
                    'x-service-name' => config('app.name'),
                    'x-request-id' => $payload['request_id'],
                    'Accept' => 'application/json',
                ]);

            // In local/dev with self-signed certs, you *may* disable verification (not prod!)
            if (app()->isLocal()) {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post($endpoint, $payload);

            if ($response->failed()) {
                Log::error('SendLogToLoggingService: failed to send log', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload,
                ]);

                return;
            }

            Log::debug('SendLogToLoggingService: buffered log', [
                'service' => $payload['service'],
                'level' => $payload['level'] ?? 'info',
                'target_id' => $payload['target_id'] ?? null,
                'actor_id' => $payload['actor_id'] ?? null,
                'log_id' => $payload['log_id'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendLogToLoggingService: failed to buffer log', [
                'err' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }

    public function failed(object $event, \Throwable $exception): void
    {
        Log::critical('SendLogToLoggingService failed', [
            'err' => $exception->getMessage(),
            'payload' => $event->payload->toArray(),
        ]);
    }
}
