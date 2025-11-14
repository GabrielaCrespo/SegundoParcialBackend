<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;
use Exception;

class GrupoController extends Controller
{
    protected Grupo $grupoModel;

    public function __construct()
    {
        $this->grupoModel = new Grupo();
    }

    /**
     * Listar todos los grupos
     */
    public function index(Request $request)
    {
        try {
            $grupos = $this->grupoModel->findAll();         

            return response()->json([
                'success' => true,
                'data' => $grupos
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grupos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un grupo específico
     */
    public function show($id)
    {
        try {
            $grupo = $this->grupoModel->findById((int)$id);
            
            if (!$grupo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $grupo
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo grupo
     */
    public function store(Request $request)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'nombre_grupo' => 'required|string|max:20',
                'idmateria' => 'required|integer',
                'idgestion' => 'required|integer',
                'capacidad' => 'nullable|integer|min:1'
            ]);

            // Verificar si ya existe un grupo con ese nombre para la misma materia y gestión
            if ($this->grupoModel->existsByNombreMateriaGestion(
                $validated['nombre_grupo'],
                $validated['idmateria'],
                $validated['idgestion']
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un grupo con ese nombre para esta materia y gestión'
                ], 409);
            }

            // Crear el grupo
            $grupo = $this->grupoModel->create($validated);
            
            // Obtener información completa del grupo creado
            $grupoCompleto = $this->grupoModel->findById($grupo['idgrupo']);
            
            // Registrar en bitácora
            activity('Grupos')
                ->withProperties([
                    'idgrupo' => $grupo['idgrupo'],
                    'nombre_grupo' => $grupo['nombre_grupo'],
                    'materia' => $grupoCompleto['materia_nombre'] ?? '',
                    'gestion' => ($grupoCompleto['anio'] ?? '') . ' - ' . ($grupoCompleto['periodo'] ?? '')
                ])
                ->log("Grupo creado: {$grupo['nombre_grupo']} - {$grupoCompleto['materia_nombre']}");

            return response()->json([
                'success' => true,
                'message' => 'Grupo creado exitosamente',
                'data' => $grupoCompleto
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un grupo existente
     */
    public function update(Request $request, $id)
    {
        try {
            // Validar que el grupo existe
            // Obtener el grupo existente
            $grupoExistente = $this->grupoModel->findById((int)$id);
            if (!$grupoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo no encontrado'
                ], 404);
            }

            // Validar datos
            $validated = $request->validate([
                'nombre_grupo' => 'required|string|max:20',
                'idmateria' => 'sometimes|integer|exists:materia,idmateria',
                'idgestion' => 'sometimes|integer|exists:gestion,idgestion',
                'capacidad' => 'sometimes|integer|min:1'
            ]);

            // Si se está cambiando el nombre, verificar que no exista otro grupo con ese nombre en la misma materia/gestión
            if (isset($validated['nombre_grupo']) && $validated['nombre_grupo'] !== $grupoExistente['nombre_grupo']) {
                $idmateria = $validated['idmateria'] ?? $grupoExistente['idmateria'];
                $idgestion = $validated['idgestion'] ?? $grupoExistente['idgestion'];
                
                if ($this->grupoModel->existsByNombreMateriaGestion($validated['nombre_grupo'], $idmateria, $idgestion)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un grupo con ese nombre para esta materia y gestión'
                    ], 409);
                }
            }

            // Actualizar el grupo
            $grupo = $this->grupoModel->update((int)$id, $validated);
            
            // Registrar en bitácora
            activity('Grupos')
                ->withProperties([
                    'idgrupo' => $id,
                    'nombre_grupo' => $grupo['nombre_grupo']
                ])
                ->log("Grupo actualizado: {$grupo['nombre_grupo']}");
            
            return response()->json([
                'success' => true,
                'message' => 'Grupo actualizado exitosamente',
                'data' => $grupo
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el grupo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un grupo
     */
    public function destroy($id)
    {
        try {
            // Validar que el grupo existe
            $grupo = $this->grupoModel->findById((int)$id);
            
            if (!$grupo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo no encontrado'
                ], 404);
            }

            // Eliminar el grupo
            $this->grupoModel->delete((int)$id);
            
            // Registrar en bitácora
            activity('Grupos')
                ->withProperties([
                    'idgrupo' => $id,
                    'nombre_grupo' => $grupo['nombre_grupo']
                ])
                ->log("Grupo eliminado: {$grupo['nombre_grupo']}");
           
            return response()->json([
                'success' => true,
                'message' => 'Grupo eliminado exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el grupo. Puede tener asignaciones asociadas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar grupos por nombre
     */
    public function search(Request $request)
    {
        try {
            $nombre = $request->query('nombre', '');
            $idmateria = $request->query('idmateria');
            $idgestion = $request->query('idgestion');
            
            if (empty($nombre)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar un nombre para buscar'
                ], 400);
            }

            // Si se proporciona materia y gestión, buscar en ese scope
            if ($idmateria && $idgestion) {
                $grupos = $this->grupoModel->findByMateriaYGestion((int)$idmateria, (int)$idgestion);
                // Filtrar por nombre
                $grupos = array_filter($grupos, function($grupo) use ($nombre) {
                    return stripos($grupo['nombre_grupo'], $nombre) !== false;
                });
            } else {
                // Buscar en todos los grupos
                $todosGrupos = $this->grupoModel->findAll();
                $grupos = array_filter($todosGrupos, function($grupo) use ($nombre) {
                    return stripos($grupo['nombre_grupo'], $nombre) !== false;
                });
            }

            return response()->json([
                'success' => true,
                'data' => array_values($grupos)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar grupos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}