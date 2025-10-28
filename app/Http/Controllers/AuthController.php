<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            $email = $request->input('email');
            $password = $request->input('password');
            
            // Validar campos requeridos
            if (!$email || !$password) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email y password son requeridos'
                ], 400);
            }
            
            // Buscar usuario por email
            $user = $this->userModel->findByEmail($email);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }
            
            // Verificar si el usuario está activo
            if (!$user['activo']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario inactivo'
                ], 401);
            }
            
            // Verificar password
            if (!$this->userModel->verifyPassword($password, $user['password'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }
            
            // Generar token JWT
            $tokenData = [
                'user_id' => $user['idusuario'],
                'email' => $user['email'],
                'role' => $user['rol_nombre'],
                'role_id' => $user['idrol']
            ];
            
            $token = $this->jwtService->generateToken($tokenData);
            
            // Remover password de la respuesta
            unset($user['password']);
            
            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
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
     * CU1 - Cerrar Sesión
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // En un sistema JWT sin blacklist, simplemente respondemos exitosamente
            // El cliente debe eliminar el token del storage
            return response()->json([
                'success' => true,
                'message' => 'Logout exitoso'
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
                'data' => [
                    'token' => $newToken
                ]
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
                    'message' => 'Token inválido'
                ], 401);
            }
            
            $user = $this->userModel->findById($payload['user_id']);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            // Remover password de la respuesta
            unset($user['password']);
            
            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}