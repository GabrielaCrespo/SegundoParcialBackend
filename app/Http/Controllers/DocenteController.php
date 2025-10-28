<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocenteController extends Controller
{
    private $docenteModel;
    private $roleModel;
    
    public function __construct()
    {
        $this->docenteModel = new Docente();
        $this->roleModel = new Role();
    }
    
    /**
     * CU5 - Listar todos los docentes
     */
    public function index(): JsonResponse
    {
        try {
            $docentes = $this->docenteModel->findAll();
            
            return response()->json([
                'success' => true,
                'data' => $docentes
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener docentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU5 - Obtener un docente por ID
     */
    public function show($id): JsonResponse
    {
        try {
            $docente = $this->docenteModel->findById($id);
            
            if (!$docente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Docente no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $docente
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU5 - Registrar nuevo docente
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
            
            // Preparar datos específicos del docente
            $docenteData = [
                'especialidad' => $request->input('especialidad'),
                'fechacontrato' => $request->input('fechacontrato')
            ];
            
            $docente = $this->docenteModel->create($userData, $docenteData);
            
            if (!$docente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear docente'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Docente registrado exitosamente',
                'data' => $docente
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU6 - Actualizar datos del docente
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Verificar que el docente existe
            $existingDocente = $this->docenteModel->findById($id);
            if (!$existingDocente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Docente no encontrado'
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
            
            // Preparar datos específicos del docente si se proporcionan
            $docenteData = null;
            if ($request->has('especialidad') || $request->has('fechacontrato')) {
                $docenteData = [];
                
                if ($request->has('especialidad')) $docenteData['especialidad'] = $request->input('especialidad');
                if ($request->has('fechacontrato')) $docenteData['fechacontrato'] = $request->input('fechacontrato');
            }
            
            $docente = $this->docenteModel->update($id, $userData, $docenteData);
            
            if (!$docente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar docente'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Docente actualizado exitosamente',
                'data' => $docente
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU5 - Eliminar docente
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Verificar que el docente existe
            $docente = $this->docenteModel->findById($id);
            if (!$docente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Docente no encontrado'
                ], 404);
            }
            
            $result = $this->docenteModel->delete($id);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar docente'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Docente eliminado exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar docente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Buscar docentes por especialidad
     */
    public function searchByEspecialidad(Request $request): JsonResponse
    {
        try {
            $especialidad = $request->input('especialidad');
            
            if (!$especialidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'El parámetro especialidad es requerido'
                ], 400);
            }
            
            $docentes = $this->docenteModel->findByEspecialidad($especialidad);
            
            return response()->json([
                'success' => true,
                'data' => $docentes
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar docentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}