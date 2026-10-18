<?php

use App\Services\Applications\Caches\UserCacheService;

if (! function_exists('authId')) {
    function authId(): ?int
    {
        return (int) (request()->attributes->get('jwt_claims')['sub'] ?? null);
    }
}

if (! function_exists('assetUrl')) {
    function assetUrl(string $path, ?string $baseUrl = null): string
    {
        return rtrim($baseUrl ?? env('AUTH_SERVICE_MAIN_URI'), '/').'/'.ltrim($path, '/');
    }
}

if (! function_exists('user')) {
    function user(): UserCacheService
    {
        return app(UserCacheService::class);
    }
}
