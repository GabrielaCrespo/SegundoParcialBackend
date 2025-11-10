<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class JWTService
{
    private string $secret;
    private string $alg = 'HS256';
    private string $iss;
    private string $aud;
    private int $ttl;         // minutos de vida del access token
    private int $refreshTtl;  // minutos de ventana para refrescar
    private int $leeway;      // segundos de tolerancia de reloj

    public function __construct()
    {
        // NO usar APP_KEY; usa tu propio JWT_SECRET
        $this->secret     = (string) env('JWT_SECRET', 'change_this_secret');
        $this->iss        = (string) env('JWT_ISS', 'segundo-parcial-backend');
        $this->aud        = (string) env('JWT_AUD', 'segundo-parcial-frontend');
        $this->ttl        = (int) env('JWT_TTL_MIN', 1440);        // 24 h
        $this->refreshTtl = (int) env('JWT_REFRESH_TTL_MIN', 43200); // 30 días
        $this->leeway     = (int) env('JWT_LEEWAY_SEC', 30);
        JWT::$leeway      = $this->leeway;
    }

    public function generateToken(array $claims): string
    {
        $now = time();

        $payload = array_merge($claims, [
            'iss' => $this->iss,
            'aud' => $this->aud,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + ($this->ttl * 60),
        ]);

        return JWT::encode($payload, $this->secret, $this->alg);
    }

    /** Devuelve el payload como array o null si inválido/expirado */
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($this->secret, $this->alg));
            return json_decode(json_encode($decoded), true);
        } catch (\Throwable $e) {
            \Log::warning('JWT validate error', ['msg' => $e->getMessage()]);
            return null;
        }
    }


    /** Nuevo token si sigue dentro de la ventana de refresh; null si no */
    public function refreshToken(string $token): ?string
    {
        $payload = $this->validateToken($token);
        if (!$payload) return null;

        $issuedAt = (int)($payload['iat'] ?? 0);
        if ($issuedAt + ($this->refreshTtl * 60) < time()) {
            return null; // fuera de ventana de refresh
        }

        // Reemite con los mismos claims de negocio
        unset($payload['iss'], $payload['aud'], $payload['iat'], $payload['nbf'], $payload['exp']);

        return $this->generateToken($payload);
    }

    public function extractTokenFromHeader(\Illuminate\Http\Request $request): ?string
{
    // a) Header estándar
    $auth = $request->header('Authorization');

    // b) Fallback común en algunos servidores/proxies
    if (!$auth) {
        $auth = $request->server('HTTP_AUTHORIZATION');
    }

    // c) Normaliza y extrae Bearer
    if ($auth) {
        $auth = trim(preg_replace('/\s+/', ' ', $auth));
        if (stripos($auth, 'Bearer ') === 0) {
            return trim(substr($auth, 7));
        }
    }

    // d) Fallback de depuración: ?token=...
    $qp = $request->query('token');
    if (is_string($qp) && $qp !== '') {
        return $qp;
    }

    return null;
}


}
