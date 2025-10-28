<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController  extends Controller
{
    private $roleModel;
    private $permisoModel;
    
    public function __construct()
    {
        $this->roleModel = new Role();
        $this->permisoModel = new Permiso();
    }
    
    /**
     * CU3 - Listar todos los roles
     */
    public function index(): JsonResponse
    {
        try {
            $roles = $this->roleModel->findAll();
            
            return response()->json([
                'success' => true,
                'data' => $roles
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU3 - Obtener un rol por ID
     */
    public function show($id): JsonResponse
    {
        try {
            $role = $this->roleModel->findById($id);
            
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $role
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU3 - Crear nuevo rol
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $nombre = $request->input('nombre');
            $descripcion = $request->input('descripcion');
            
            if (!$nombre) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nombre del rol es requerido'
                ], 400);
            }
            
            // Validar nombre único
            $existingRole = $this->roleModel->findByNombre($nombre);
            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un rol con ese nombre'
                ], 400);
            }
            
            $roleData = [
                'nombre' => $nombre,
                'descripcion' => $descripcion
            ];
            
            $role = $this->roleModel->create($roleData);
            
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear rol'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Rol creado exitosamente',
                'data' => $role
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU3 - Actualizar rol
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Verificar que el rol existe
            $existingRole = $this->roleModel->findById($id);
            if (!$existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            
            $nombre = $request->input('nombre');
            $descripcion = $request->input('descripcion');
            
            if (!$nombre) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nombre del rol es requerido'
                ], 400);
            }
            
            // Validar nombre único (excluyendo el rol actual)
            $existingName = $this->roleModel->findByNombre($nombre);
            if ($existingName && $existingName['idrol'] != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otro rol con ese nombre'
                ], 400);
            }
            
            $roleData = [
                'nombre' => $nombre,
                'descripcion' => $descripcion
            ];
            
            $role = $this->roleModel->update($id, $roleData);
            
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar rol'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Rol actualizado exitosamente',
                'data' => $role
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU3 - Eliminar rol
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Verificar que el rol existe
            $role = $this->roleModel->findById($id);
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            
            $result = $this->roleModel->delete($id);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar rol'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU3 - Obtener permisos de un rol
     */
    public function getPermissions($id): JsonResponse
    {
        try {
            if (!$this->roleModel->exists($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            
            $permissions = $this->roleModel->getRolePermissions($id);
            
            return response()->json([
                'success' => true,
                'data' => $permissions
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos del rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU3 - Asignar permiso a rol
     */
    public function assignPermission(Request $request, $id): JsonResponse
    {
        try {
            $idpermiso = $request->input('idpermiso');
            
            if (!$idpermiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'El ID del permiso es requerido'
                ], 400);
            }
            
            // Verificar que el rol existe
            if (!$this->roleModel->exists($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            
            // Verificar que el permiso existe
            if (!$this->permisoModel->exists($idpermiso)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }
            
            // Verificar si ya tiene el permiso
            if ($this->roleModel->hasPermission($id, $idpermiso)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol ya tiene este permiso asignado'
                ], 400);
            }
            
            $result = $this->roleModel->assignPermission($id, $idpermiso);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al asignar permiso'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Permiso asignado exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CU3 - Remover permiso de rol
     */
    public function removePermission(Request $request, $id): JsonResponse
    {
        try {
            $idpermiso = $request->input('idpermiso');
            
            if (!$idpermiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'El ID del permiso es requerido'
                ], 400);
            }
            
            // Verificar que el rol existe
            if (!$this->roleModel->exists($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            
            // Verificar que el permiso existe
            if (!$this->permisoModel->exists($idpermiso)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }
            
            // Verificar si tiene el permiso
            if (!$this->roleModel->hasPermission($id, $idpermiso)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol no tiene este permiso asignado'
                ], 400);
            }
            
            $result = $this->roleModel->removePermission($id, $idpermiso);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al remover permiso'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Permiso removido exitosamente'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al remover permiso',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}