<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SessionToken;
use App\Services\JWTService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    private $userModel;
    private $jwtService;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->jwtService = new JWTService();
    }

    /**
     * CU1 - Iniciar Sesión
     */
       public function login(Request $request): JsonResponse
    {
        try {
            $email = trim((string) $request->input('email', ''));
            $password = (string) $request->input('password', '');

            if ($email === '' || $password === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Email y password son requeridos'
                ], 400);
            }

            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            $activo = ($user['activo'] === true) || ($user['activo'] === 1) ||
                      ($user['activo'] === '1')   || ($user['activo'] === 't') ||
                      ($user['activo'] === 'true');

            if (!$activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario inactivo'
                ], 401);
            }

            if (!$this->userModel->verifyPassword($password, $user['password'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            // ✅ Generar el token con nombre incluido
            $tokenData = [
                'user_id' => (int)$user['idusuario'],
                'nombre'  => $user['nombre'],
                'email'   => $user['email'],
                'role'    => $user['rol_nombre'] ?? null,
                'role_id' => isset($user['idrol']) ? (int)$user['idrol'] : null,
            ];
            $token = $this->jwtService->generateToken($tokenData);

            // ✅ Guardar token en la tabla session_tokens
            SessionToken::create([
                'idusuario' => $user['idusuario'],
                'nombre'    => $user['nombre'],
                'email'     => $user['email'],
                'token'     => $token,
            ]);

            // 🔹 Registrar acción en bitácora (login)
            activity('Autenticación')
                ->withProperties([
                    'usuario' => $user['nombre'],
                    'email'   => $user['email'],
                    'idusuario' => $user['idusuario'],
                    'rol' => $user['rol_nombre'] ?? 'Sin rol'
                ])
                ->log("El usuario {$user['nombre']} inició sesión correctamente");

            unset($user['password']);

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'user'  => $user,
                    'token' => $token,
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cerrar sesión
     */
   public function logout(Request $request): JsonResponse
    {
        try {
            $jwt = new JWTService();
            $token = $jwt->extractTokenFromHeader($request);

            // 🔹 Buscar sesión activa antes de eliminar
            $sesion = $token ? SessionToken::where('token', $token)->first() : null;
            $nombreUsuario = $sesion->nombre ?? 'Desconocido';
            $emailUsuario  = $sesion->email ?? 'N/A';

            if ($token) {
                SessionToken::where('token', $token)->delete();
            }

            // 🔹 Registrar acción en bitácora (logout)
            activity('Autenticación')
                ->withProperties([
                    'usuario' => $nombreUsuario,
                    'email' => $emailUsuario,
                ])
                ->log("El usuario {$nombreUsuario} cerró sesión");

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada correctamente'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refrescar Token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $token = $this->jwtService->extractTokenFromHeader($request);
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no proporcionado'
                ], 401);
            }

            $newToken = $this->jwtService->refreshToken($token);
            if (!$newToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token refrescado exitosamente',
                'data' => ['token' => $newToken]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $token = $this->jwtService->extractTokenFromHeader($request);
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no proporcionado'
                ], 401);
            }

            $payload = $this->jwtService->validateToken($token);
            if (!$payload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido o expirado'
                ], 401);
            }

            $userId = isset($payload['user_id']) ? (int) $payload['user_id'] : 0;
            if ($userId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido: no contiene ID de usuario válido'
                ], 401);
            }

            $user = $this->userModel->findById($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            unset($user['password']);

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
