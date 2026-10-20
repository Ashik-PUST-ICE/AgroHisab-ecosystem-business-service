<?php

namespace Tests\Traits;

use App\Http\Middleware\AuthenticateWithJwt;
use Closure;
use Illuminate\Http\Request;

trait WithJwtAuth
{
    protected function authenticateJwt(int $userId = 1, int $companyId = 1): self
    {
        $this->app->instance(AuthenticateWithJwt::class, new class($userId, $companyId) {
            public function __construct(private int $userId, private int $companyId) {}

            public function handle(Request $request, Closure $next)
            {
                $request->attributes->set('jwt_claims', [
                    'sub' => $this->userId,
                    'company_id' => $this->companyId,
                ]);

                return $next($request);
            }
        });

        return $this;
    }
}
