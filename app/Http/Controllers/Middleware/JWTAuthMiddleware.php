<?php
namespace App\Http\Middleware;

use Closure;
use App\Services\JWTService;

class JWTAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $jwt = new JWTService();
        $token = $jwt->extractTokenFromHeader($request);
        $payload = $token ? $jwt->validateToken($token) : null;

        if ($payload && isset($payload['user_id'])) {
            $request->attributes->set('usuario_auth', [
                'id'     => $payload['user_id'],
                'nombre' => $payload['nombre'] ?? 'Sistema',
                'email'  => $payload['email'] ?? null,
            ]);
        }

        return $next($request);
    }
}
