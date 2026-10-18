<?php

namespace App\Services\Business\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $projectId;
    protected ?string $accessToken;
    protected int $tokenExpiresAt;

    public function __construct()
    {
        $this->projectId = env('FCM_PROJECT_ID', '');
        $this->accessToken = null;
        $this->tokenExpiresAt = 0;
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (empty($this->projectId)) {
            Log::warning('FcmService: FCM_PROJECT_ID not configured');

            return false;
        }

        $accessToken = $this->getAccessToken();

        if (! $accessToken) {
            Log::warning('FcmService: unable to obtain access token');

            return false;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'android' => [
                    'priority' => 'high',
                ],
                'apns' => [
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                ],
            ],
        ];

        try {
            $response = Http::timeout(10)
                ->withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $payload);

            if ($response->successful()) {
                Log::info('FcmService: notification sent', ['token' => $token]);

                return true;
            }

            Log::warning('FcmService: send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('FcmService: send exception', ['err' => $e->getMessage()]);

            return false;
        }
    }

    protected function getAccessToken(): ?string
    {
        if ($this->accessToken && time() < $this->tokenExpiresAt) {
            return $this->accessToken;
        }

        $credentialsPath = env('GOOGLE_APPLICATION_CREDENTIALS', base_path('firebase-credentials.json'));

        if (! file_exists($credentialsPath)) {
            Log::warning('FcmService: credentials file missing', ['path' => $credentialsPath]);

            return null;
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);

        if (! $credentials || ! isset($credentials['client_email'], $credentials['private_key'])) {
            Log::warning('FcmService: invalid credentials format');

            return null;
        }

        $jwt = $this->createJwt($credentials);

        try {
            $response = Http::timeout(10)
                ->asForm()
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'] ?? null;
                $this->tokenExpiresAt = time() + ($data['expires_in'] ?? 3600) - 60;

                return $this->accessToken;
            }

            Log::warning('FcmService: token request failed', ['body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::error('FcmService: token request exception', ['err' => $e->getMessage()]);
        }

        return null;
    }

    protected function createJwt(array $credentials): string
    {
        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $unsigned = "{$header}.{$claims}";
        $privateKey = $credentials['private_key'];
        $signature = '';

        if (function_exists('openssl_sign')) {
            openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $signature = $this->base64UrlEncode($signature);
        }

        return "{$unsigned}.{$signature}";
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
