<?php

namespace App\Services\Applications\Caches;

use App\Services\Applications\Gateway\MachineTokenManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserCacheService
{
    protected string $authBase;

    protected string $authToken;

    protected string $namespace;

    protected int $ttl;

    public function __construct()
    {
        $tokens = app(MachineTokenManager::class);
        $this->authBase = rtrim(config('gateway.services.auth_service.base_uri', ''), '/');
        $this->namespace = env('CACHE_USER_NAMESPACE', 'business_service');
        $this->ttl = (int) env('CACHE_USER_TTL', 600);
        $aud = (string) data_get(config('gateway.services'), 'auth_service.token_service', 'auth-service');
        $this->authToken = $tokens?->get($aud) ?? '';
    }

    /**
     * Get single user (cache -> auth fallback -> cache)
     */
    public function getUser(int|string|null $userId, array $columns = ['*']): ?array
    {
        if (empty($userId)) {
            return null;
        }

        $userId = (int) $userId;
        $key = $this->key($userId);

        if ($cached = Cache::get($key)) {
            return $columns !== ['*'] ? Arr::only($cached, $columns) : $cached;
        }

        try {
            $user = $this->fetchFromAuth($userId);
        } catch (\Throwable $e) {
            Log::error("UserCacheService: Auth call failed for user {$userId}: ".$e->getMessage());

            return null;
        }

        if (! empty($user)) {
            $this->store($userId, $user);

            return $columns !== ['*'] ? Arr::only($user, $columns) : $user;
        }

        return null;
    }

    /**
     * Bulk get users. Returns associative array [id => user|null]
     * It will try cache first, then bulk fetch missing ones (if auth supports bulk),
     * otherwise it will do concurrent requests and store results to cache.
     *
     * @param  int[]  $userIds
     * @return array<int, array|null>
     */
    public function getUsers(array $userIds, array $columns = ['*']): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if (empty($userIds)) {
            return [];
        }

        $keys = array_map(fn ($id) => $this->key($id), $userIds);
        $cachedValues = Cache::many($keys);
        $result = [];
        $missing = [];

        foreach ($userIds as $id) {
            $val = $cachedValues[$this->key($id)] ?? null;
            if ($val !== null) {
                $result[$id] = $val;
            } else {
                $result[$id] = null;
                $missing[] = $id;
            }
        }

        if (! empty($missing)) {
            $bulkResponse = $this->fetchBulkFromAuth($missing);

            if ($bulkResponse !== null && is_array($bulkResponse)) {
                $usersById = Arr::keyBy($bulkResponse, 'id');
                foreach ($missing as $id) {
                    if (isset($usersById[$id])) {
                        $user = $usersById[$id];
                        $this->store($id, $user);
                        $result[$id] = $user;
                    }
                }
                $missing = array_values(array_filter($missing, fn ($x) => ! isset($usersById[$x])));
            }
        }

        if (! empty($missing)) {
            $pool = Http::pool(function ($pool) use ($missing) {
                foreach ($missing as $id) {
                    $pool->as((string) $id)->withToken($this->authToken)->get("{$this->authBase}/api/users/{$id}");
                }
            });

            foreach ($missing as $id) {
                $resp = $pool[(string) $id] ?? null;
                if ($resp && $resp->successful()) {
                    $user = $resp->json();
                    $this->store($id, $user);
                    $result[$id] = $user;
                } else {
                    $result[$id] = null;
                    Log::warning("UserCacheService: failed to fetch user {$id} (status: ".($resp?->status() ?? 'n/a').')');
                }
            }
        }

        if ($columns !== ['*']) {
            foreach ($result as $id => $val) {
                if (is_array($val)) {
                    $result[$id] = Arr::only($val, $columns);
                } else {
                    $result[$id] = null;
                }
            }
        }

        return $result;
    }

    /**
     * Warm up cache for a set of user ids (fetch + store).
     */
    public function warmUp(array $userIds): void
    {
        $this->getUsers($userIds);
    }

    /**
     * Force refresh a single user (bypass cache)
     */
    public function refreshUser(int $userId): ?array
    {
        Cache::forget($this->key($userId));

        return $this->getUser($userId);
    }

    /**
     * Forget user in cache
     */
    public function forgetUser(int $userId): void
    {
        Cache::forget($this->key($userId));
    }

    /**
     * Store user into cache (internal helper).
     * Removes any sensitive fields automatically.
     */
    protected function store(int $userId, array $user): void
    {
        // minimal projection - strip sensitive fields if present
        $safe = $this->projectSafe($user);
        $ttl = $this->ttl + rand(10, 60); // jitter to avoid thundering herd

        Cache::put($this->key($userId), $safe, $ttl);
    }

    /**
     * Safely project user object to cacheable form (remove tokens/passwords, keep minimal fields).
     */
    protected function projectSafe(array $user): array
    {
        // fields you want to cache - add more if your services need them
        $allowed = [
            'id',
            'full_name',
            'first_name',
            'last_name',
            'email',
            'email_verified_at',
            'unique_id',
            'mobile',
            'mobile_verified_at',
            'whatsapp',
            'whatsapp_verified_at',
            'image',
            'status',
            'is_employee',
            'meta',
            'updated_at',
        ];

        $safe = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $user)) {
                $safe[$f] = $user[$f];
            }
        }

        // ensure id remains
        if (! isset($safe['id']) && isset($user['id'])) {
            $safe['id'] = (int) $user['id'];
        }

        // normalize roles/meta if present
        if (isset($safe['roles']) && ! is_array($safe['roles'])) {
            $safe['roles'] = is_string($safe['roles']) ? json_decode($safe['roles'], true) ?? [] : (array) $safe['roles'];
        }
        if (isset($safe['meta']) && ! is_array($safe['meta'])) {
            $safe['meta'] = is_string($safe['meta']) ? json_decode($safe['meta'], true) ?? [] : (array) $safe['meta'];
        }

        return $safe;
    }

    /**
     * Fetch a single user from Auth (best-effort)
     */
    protected function fetchFromAuth(int $userId): ?array
    {
        if (empty($this->authBase)) {
            return null;
        }
        try {
            $response = Http::timeout(4)
                ->withToken($this->authToken)
                ->acceptJson()
                ->get("{$this->authBase}/v1/microservices/users/{$userId}");

            if ($response->successful()) {
                $output = $response->json();

                return $output['data'] ?? null;
            }

            Log::warning("UserCacheService: auth returned status {$response->status()} for user {$userId}");

            return null;
        } catch (Throwable $e) {
            Log::error("UserCacheService: auth unavailable for user {$userId}: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Try a bulk fetch from auth: expects auth to support /api/users?ids=1,2,3
     * Returns array|null
     */
    protected function fetchBulkFromAuth(array $ids): ?array
    {
        if (empty($this->authBase)) {
            return null;
        }
        $ids = array_values($ids);
        try {
            $qs = http_build_query(['ids' => implode(',', $ids)]);
            $response = Http::timeout(6)
                ->withToken($this->authToken)
                ->acceptJson()
                ->get("{$this->authBase}/v1/microservices/users?{$qs}");

            if ($response->successful()) {
                $output = $response->json();
                $data = $output['data'] ?? [];
                // expect array of users or map id=>user; normalize to array of user objects
                if (Arr::isAssoc($data)) {
                    // if auth returns keyed by id, convert to list
                    return array_values($data);
                }
                if (is_array($data)) {
                    return $data;
                }
            }

            Log::warning("UserCacheService: bulk fetch failed status {$response->status()}");

            return null;
        } catch (Throwable $e) {
            Log::error('UserCacheService bulk fetch error: '.$e->getMessage());

            return null;
        }
    }

    protected function key(int $userId): string
    {
        return "{$this->namespace}:user:profile:{$userId}";
    }
}
