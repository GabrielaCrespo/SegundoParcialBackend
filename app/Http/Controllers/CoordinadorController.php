<?php

namespace App\Http\Controllers;

use App\Models\Coordinador;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CoordinadorController extends Controller
{
    private $coordinadorModel;
    private $roleModel;
    
    public function __construct()
    {
        $this->coordinadorModel = new Coordinador();
        $this->roleModel = new Role();
    }
    
    /**
     * Listar todos los coordinadores
     */
    public function index(): JsonResponse
    {
        try {
            $coordinadores = $this->coordinadorModel->findAll();
            
            return response()->json([
                'success' => true,
                'data' => $coordinadores
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener coordinadores',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener un coordinador por ID
     */
    public function show($id): JsonResponse
    {
        try {
            $coordinador = $this->coordinadorModel->findById($id);
            
            if (!$coordinador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coordinador no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $coordinador
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener coordinador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Registrar nuevo coordinador
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validaciones básicas para usuario
            $nombre = $request->input('nombre');
            $username = $request->input('username');
            $email = $request->input('email');
            $password = $request->input('password');
            $idrol = $request->input('idrol');
            
            if (!$nombre || !$username || !$email || !$password || !$idrol) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los campos nombre, username, email, password e idrol son requeridos'
                ], 400);
            }
            
            // Validar que el rol existe
            if (!$this->roleModel->exists($idrol)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol especificado no existe'
                ], 400);
            }
            
            // Preparar datos del usuario
            $userData = [
                'nombre' => $nombre,
                'celular' => $request->input('celular'),
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'activo' => $request->input('activo', true),
                'idrol' => $idrol
            ];
            
            // Preparar datos específicos del coordinador
            $coordinadorData = [
                'fechacontrato' => $request->input('fechacontrato')
            ];
            
            $coordinador = $this->coordinadorModel->create($userData, $coordinadorData);
            
            if (!$coordinador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear coordinador'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Coordinador registrado exitosamente',
                'data' => $coordinador
            ], 201)->header('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar coordinador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualizar coordinador
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Verificar que el coordinador existe
            $existingCoordinador = $this->coordinadorModel->findById($id);
            if (!$existingCoordinador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coordinador no encontrado'
                ], 404);
            }
            
            // Preparar datos del usuario si se proporcionan
            $userData = null;
            if ($request->has('nombre') || $request->has('username') || $request->has('email') || 
                $request->has('celular') || $request->has('activo') || $request->has('idrol')) {
                
                $userData = [];
                
                if ($request->has('nombre')) $userData['nombre'] = $request->input('nombre');
                if ($request->has('celular')) $userData['celular'] = $request->input('celular');
                if ($request->has('username')) $userData['username'] = $request->input('username');
                if ($request->has('email')) $userData['email'] = $request->input('email');
                if ($request->has('activo')) $userData['activo'] = $request->input('activo');
                if ($request->has('idrol')) {
                    $idrol = $request->input('idrol');
                    if (!$this->roleModel->exists($idrol)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'El rol especificado no existe'
                        ], 400);
                    }
                    $userData['idrol'] = $idrol;
                }
                
                if ($request->has('password')) {
                    $userData['password'] = $request->input('password');
                }
            }
            
            // Preparar datos específicos del coordinador si se proporcionan
            $coordinadorData = null;
            if ($request->has('fechacontrato')) {
                $coordinadorData = [
                    'fechacontrato' => $request->input('fechacontrato')
                ];
            }
            
            $coordinador = $this->coordinadorModel->update($id, $userData, $coordinadorData);
            
            if (!$coordinador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar coordinador'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Coordinador actualizado exitosamente',
                'data' => $coordinador
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar coordinador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar coordinador
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Verificar que el coordinador existe
            $coordinador = $this->coordinadorModel->findById($id);
            if (!$coordinador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coordinador no encontrado'
                ], 404);
            }
            
            $result = $this->coordinadorModel->delete($id);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar coordinador'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Coordinador eliminado exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar coordinador',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}