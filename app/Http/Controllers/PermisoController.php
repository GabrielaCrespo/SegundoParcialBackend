<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PermisoController extends Controller
{
    private $permisoModel;
    
    public function __construct()
    {
        $this->permisoModel = new Permiso();
    }
    
    /**
     * Listar todos los permisos
     */
    public function index(): JsonResponse
    {
        try {
            $permisos = $this->permisoModel->findAll();
            
            return response()->json([
                'success' => true,
                'data' => $permisos
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener un permiso por ID
     */
    public function show($id): JsonResponse
    {
        try {
            $permiso = $this->permisoModel->findById($id);
            
            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $permiso
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crear nuevo permiso
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $nombre = $request->input('nombre');
            $descripcion = $request->input('descripcion');
            
            if (!$nombre) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nombre del permiso es requerido'
                ], 400);
            }
            
            // Validar nombre único
            $existingPermiso = $this->permisoModel->findByNombre($nombre);
            if ($existingPermiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un permiso con ese nombre'
                ], 400);
            }
            
            $permisoData = [
                'nombre' => $nombre,
                'descripcion' => $descripcion
            ];
            
            $permiso = $this->permisoModel->create($permisoData);
            
            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear permiso'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Permiso creado exitosamente',
                'data' => $permiso
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualizar permiso
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Verificar que el permiso existe
            $existingPermiso = $this->permisoModel->findById($id);
            if (!$existingPermiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }
            
            $nombre = $request->input('nombre');
            $descripcion = $request->input('descripcion');
            
            if (!$nombre) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nombre del permiso es requerido'
                ], 400);
            }
            
            // Validar nombre único (excluyendo el permiso actual)
            $existingName = $this->permisoModel->findByNombre($nombre);
            if ($existingName && $existingName['idpermiso'] != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otro permiso con ese nombre'
                ], 400);
            }
            
            $permisoData = [
                'nombre' => $nombre,
                'descripcion' => $descripcion
            ];
            
            $permiso = $this->permisoModel->update($id, $permisoData);
            
            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar permiso'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Permiso actualizado exitosamente',
                'data' => $permiso
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar permiso
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Verificar que el permiso existe
            $permiso = $this->permisoModel->findById($id);
            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }
            
            $result = $this->permisoModel->delete($id);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar permiso'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Permiso eliminado exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener roles que tienen un permiso específico
     */
    public function getRoles($id): JsonResponse
    {
        try {
            if (!$this->permisoModel->exists($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }
            
            $roles = $this->permisoModel->getRolesByPermiso($id);
            
            return response()->json([
                'success' => true,
                'data' => $roles
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles del permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}