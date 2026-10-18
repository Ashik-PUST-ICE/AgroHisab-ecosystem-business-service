<?php

namespace App\Services\Applications\Gateway;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MachineTokenManager
{
    /**
     * Get a cached client_credentials token for calling another service.
     * Uses gateway.php settings:
     * - token_url, client_id, client_secret
     * - machine_token_ttl (minutes) as cache floor if expires_in absent
     *
     * @param  string|null  $audience  e.g. "log-service" or "logging-service"
     * @param  array  $scopes  e.g. ['gateway:proxy'] (optional)
     * @return string access_token
     */
    public function get(?string $audience = null, array $scopes = []): string
    {
        $tokenUrl = (string) config('gateway.token_url');
        $clientId = (string) config('gateway.client_id');
        $clientSecret = (string) config('gateway.client_secret');

        abort_unless($tokenUrl && $clientId && $clientSecret, 500, 'Machine token config missing (token_url/client_id/client_secret)');

        // Default “audience” = the friendly name you set in config('gateway.services.*.token_service')
        // If not provided here, you likely pass it from the caller.
        $aud = $audience ?: null; // null is allowed if your Auth server doesn’t require aud

        // Optional scopes
        $scopeStr = implode(' ', $scopes);

        $cacheKey = 'mtok:'.md5($clientId.'|'.($aud ?? '').'|'.$scopeStr);
        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        $payload = array_filter([
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'audience' => $aud,
            'scope' => $scopeStr ?: null,
        ]);

        $res = Http::asForm()
            ->withoutVerifying()
            ->timeout((float) config('gateway.cb.timeout', 3.0))
            ->retry((int) config('gateway.cb.retries', 1), (int) config('gateway.cb.retry_delay', 150))
            ->post($tokenUrl, $payload);

        if (! $res->successful()) {
            Log::error('Machine token request failed', [
                'status' => $res->status(),
                'body' => $res->body(),
                'url' => $tokenUrl,
                'payload' => $payload,
            ]);
            abort(500, 'Machine token HTTP '.$res->status());
        }

        $json = $res->json();
        $access = Arr::get($json, 'access_token');
        $expiresIn = (int) Arr::get($json, 'expires_in', 60 * (int) config('gateway.machine_token_ttl', 8));

        abort_unless(is_string($access) && $access !== '', 500, 'Machine token missing access_token');

        // Safety: shave ~45s to avoid using near-expiry tokens
        $ttl = max(60, $expiresIn - 45);
        Cache::put($cacheKey, $access, now()->addSeconds($ttl));

        return $access;
    }
}
