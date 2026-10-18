<?php

namespace App\Http\Middleware;

use Closure;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser as JwtParser;
use Lcobucci\JWT\Validation\Constraint;
use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->header('Authorization', '');
        abort_if(! str_starts_with($auth, 'Bearer '), 401, 'Missing Bearer token');

        $jwtStr = substr($auth, 7);
        $parser = new JwtParser(new JoseEncoder);
        $token = $parser->parse($jwtStr);

        $pem = $this->resolveVerificationPem($token->headers()->get('kid'));

        $cfg = Configuration::forAsymmetricSigner(
            new Sha256,
            InMemory::plainText('unused-signing-key'),
            InMemory::plainText($pem)
        );

        $constraints = [
            new Constraint\SignedWith($cfg->signer(), $cfg->verificationKey()),
            new Constraint\StrictValidAt(
                new SystemClock(new DateTimeZone(config('app.timezone', 'UTC'))),
                new \DateInterval('PT30S')
            ),
        ];

        if ($aud = config('gateway.audience')) {
            $constraints[] = new Constraint\PermittedFor($aud);
        }

        foreach ($constraints as $c) {
            $cfg->validator()->assert($token, $c);
        }

        $tokenIss = $token->claims()->has('iss') ? (string) $token->claims()->get('iss') : null;
        $allowed = config('gateway.issuers', []);
        if (empty($allowed) && ($single = config('gateway.issuer'))) {
            $allowed = [$single];
        }
        $requireIss = (bool) config('gateway.require_iss', false);

        if ($tokenIss !== null) {
            $normTokenIss = $this->normIssuer($tokenIss);
            $normAllowed = array_map([$this, 'normIssuer'], $allowed);
            if (! empty($normAllowed) && ! in_array($normTokenIss, $normAllowed, true)) {
                abort(401, "Issuer mismatch. Token iss='{$tokenIss}'. Allowed: ".implode(', ', $allowed));
            }
        } elseif ($requireIss && ! empty($allowed)) {
            abort(401, "Token missing 'iss' claim. Allowed issuers: ".implode(', ', $allowed));
        }

        $claims = $token->claims()->all();
        $userId = $claims['sub'] ?? null;

        abort_if(! $userId, 401, 'Invalid token subject');

        $request->attributes->set('jwt_claims', $claims);

        return $next($request);
    }

    private function normIssuer(?string $s): string
    {
        $s = trim((string) $s);

        return rtrim($s, '/');
    }

    private function resolveVerificationPem(?string $kid): string
    {
        if ($jwksUrl = config('gateway.jwks_url')) {
            $keys = Cache::remember('gw.jwks', now()->addMinutes(config('gateway.jwks_ttl', 15)), function () use ($jwksUrl) {
                $res = Http::timeout(3)->retry(1, 150)->get($jwksUrl);
                abort_unless($res->successful(), 500, 'JWKS fetch HTTP '.$res->status());
                $data = $res->json();
                abort_unless(is_array($data) && isset($data['keys']) && is_array($data['keys']), 500, 'Invalid JWKS payload');

                return $data['keys'];
            });

            $key = null;
            if ($kid) {
                foreach ($keys as $k) {
                    if (($k['kid'] ?? null) === $kid) {
                        $key = $k;
                        break;
                    }
                }
                abort_unless($key, 401, 'Unknown key id (kid)');
            } else {
                foreach ($keys as $k) {
                    if (($k['kty'] ?? '') === 'RSA') {
                        $key = $k;
                        break;
                    }
                }
                abort_unless($key, 500, 'No RSA key in JWKS');
            }

            return $this->jwkToPem($key);
        }

        if ($b64 = env('AUTH_PUBLIC_PEM_INLINE_BASE64')) {
            $pem = base64_decode($b64, true) ?: '';

            return $this->sanitizePemOrFail($pem, 'inline');
        }

        if ($path = env('AUTH_PUBLIC_PEM_PATH')) {
            abort_unless(is_readable($path), 500, 'PEM path not readable');
            $pem = file_get_contents($path) ?: '';

            return $this->sanitizePemOrFail($pem, 'file');
        }

        $pemUrl = config('gateway.public_pem');
        abort_unless($pemUrl, 500, 'No JWKS or PEM configured');

        return Cache::remember('gw.passport.pem', now()->addMinutes(config('gateway.jwks_ttl', 15)), function () use ($pemUrl) {
            $res = Http::timeout(3)->retry(1, 150)->get($pemUrl);
            abort_unless($res->successful(), 500, 'PEM fetch HTTP '.$res->status());
            $pem = $res->body() ?: '';

            return $this->sanitizePemOrFail($pem, 'url');
        });
    }

    private function sanitizePemOrFail(string $pem, string $source): string
    {
        $pem = trim($pem);
        $pem = preg_replace("/\r\n|\r|\n/", "\n", $pem ?? '');

        $hasPubKey = str_contains($pem, 'BEGIN PUBLIC KEY');
        $hasRsaPub = str_contains($pem, 'BEGIN RSA PUBLIC KEY');
        $hasCert = str_contains($pem, 'BEGIN CERTIFICATE');

        abort_unless(($hasPubKey || $hasRsaPub || $hasCert),
            500,
            "Invalid PEM from $source (no BEGIN PUBLIC KEY / RSA PUBLIC KEY / CERTIFICATE). ".
                'Prefix: '.substr($pem, 0, 60)
        );

        return $pem;
    }

    private function jwkToPem(array $jwk): string
    {
        $n = new BigInteger($this->b64urlDecode($jwk['n']), 256);
        $e = new BigInteger($this->b64urlDecode($jwk['e']), 256);
        $pub = RSA::loadPublicKey(['n' => $n, 'e' => $e]);

        return $pub->toString('PKCS8');
    }

    private function b64urlDecode(string $data): string
    {
        $replaced = strtr($data, '-_', '+/');

        return base64_decode($replaced.str_repeat('=', (4 - strlen($replaced) % 4) % 4));
    }
}
