<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    private $userModel;
    private $roleModel;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->roleModel = new Role();
    }
    
    /**
     * CU2 - Listar todos los usuarios
     */
    public function index(): JsonResponse
    {
        try {
            $users = $this->userModel->getAll();
            
            // Remover passwords de la respuesta
            foreach ($users as &$user) {
                unset($user['password']);
            }
            
            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU2 - Obtener un usuario por ID
     */
    public function show($id): JsonResponse
    {
        try {
            $user = $this->userModel->findById($id);
            
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
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU2 - Crear nuevo usuario
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validaciones básicas
            $nombre = $request->input('nombre');
            $username = $request->input('username');
            $email = $request->input('email');
            $password = $request->input('password');
            $idrol = $request->input('idrol');
            
            if (!$nombre || !$username || !$email || !$password || !$idrol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Todos los campos son requeridos: nombre, username, email, password, idrol'
                ], 400);
            }
            
            // Validar email único
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ], 400);
            }
            
            // Validar username único
            $existingUsername = $this->userModel->findByUsername($username);
            if ($existingUsername) {
                return response()->json([
                    'success' => false,
                    'message' => 'El username ya está registrado'
                ], 400);
            }
            
            // Validar que el rol existe
            if (!$this->roleModel->exists($idrol)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol especificado no existe'
                ], 400);
            }
            
            // Crear usuario
            $userData = [
                'nombre' => $nombre,
                'celular' => $request->input('celular'),
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'activo' => $request->input('activo', true),
                'idrol' => $idrol
            ];
            
            $user = $this->userModel->create($userData);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear usuario'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $user
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU2 - Actualizar usuario
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Verificar que el usuario existe
            $existingUser = $this->userModel->findById($id);
            if (!$existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            // Validaciones
            $nombre = $request->input('nombre');
            $username = $request->input('username');
            $email = $request->input('email');
            $idrol = $request->input('idrol');
            
            if (!$nombre || !$username || !$email || !$idrol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los campos nombre, username, email e idrol son requeridos'
                ], 400);
            }
            
            // Validar email único (excluyendo el usuario actual)
            $existingEmail = $this->userModel->findByEmail($email);
            if ($existingEmail && $existingEmail['idusuario'] != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El email ya está registrado por otro usuario'
                ], 400);
            }
            
            // Validar username único (excluyendo el usuario actual)
            $existingUsername = $this->userModel->findByUsername($username);
            if ($existingUsername && $existingUsername['idusuario'] != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'El username ya está registrado por otro usuario'
                ], 400);
            }
            
            // Validar que el rol existe
            if (!$this->roleModel->exists($idrol)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol especificado no existe'
                ], 400);
            }
            
            // Preparar datos para actualización
            $userData = [
                'nombre' => $nombre,
                'celular' => $request->input('celular'),
                'username' => $username,
                'email' => $email,
                'activo' => $request->input('activo', true),
                'idrol' => $idrol
            ];
            
            // Agregar password solo si se proporciona
            if ($request->input('password')) {
                $userData['password'] = $request->input('password');
            }
            
            $user = $this->userModel->update($id, $userData);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar usuario'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $user
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU2 - Eliminar usuario
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Verificar que el usuario existe
            $user = $this->userModel->findById($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            $result = $this->userModel->delete($id);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar usuario'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Activar/Desactivar usuario
     */
    public function toggleStatus(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->userModel->findById($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            
            $newStatus = !$user['activo'];
            
            $updatedUser = $this->userModel->update($id, [
                'nombre' => $user['nombre'],
                'celular' => $user['celular'],
                'username' => $user['username'],
                'email' => $user['email'],
                'activo' => $newStatus,
                'idrol' => $user['idrol']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Estado de usuario actualizado exitosamente',
                'data' => $updatedUser
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}