<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Business\AuditLog\AuditLogService;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next, ?string $category = null)
    {
        $response = $next($request);

        try {
            $route = $request->route();
            $action = $route ? class_basename($route->getActionMethod() ?? $route->getName() ?? 'unknown') : 'unknown';
            $method = strtolower($request->method());
            $path = $request->path();

            app(AuditLogService::class)->log([
                'message' => "{$method} {$path}",
                'category' => $category ?: strtok($path, '/'),
                'action' => $action,
                'level' => $response->getStatusCode() >= 400 ? 'warning' : 'info',
                'context' => [
                    'method' => $method,
                    'path' => $path,
                    'status' => $response->getStatusCode(),
                    'user_id' => authId(),
                ],
            ]);
        } catch (\Throwable $e) {
            // best-effort audit logging; never break the request
        }

        return $response;
    }
}
