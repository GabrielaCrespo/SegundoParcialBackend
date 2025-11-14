<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\AsignacionHorario;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Docente;
use App\Models\Aula;
use App\Models\Gestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AsignacionController extends Controller
{
    private $asignacionModel;
    private $asignacionHorarioModel;
    private $grupoModel;
    private $materiaModel;
    private $docenteModel;
    private $aulaModel;
    private $gestionModel;

    public function __construct()
    {
        $this->asignacionModel = new Asignacion();
        $this->asignacionHorarioModel = new AsignacionHorario();
        $this->grupoModel = new Grupo();
        $this->materiaModel = new Materia();
        $this->docenteModel = new Docente();
        $this->aulaModel = new Aula();
        $this->gestionModel = new Gestion();
    }

    /**
     * Listar todas las asignaciones con filtros opcionales
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [];
            
            if ($request->query('idgestion')) {
                $filters['idgestion'] = $request->query('idgestion');
            }
            if ($request->query('iddocente')) {
                $filters['iddocente'] = $request->query('iddocente');
            }
            if ($request->query('idaula')) {
                $filters['idaula'] = $request->query('idaula');
            }
            if ($request->query('idmateria')) {
                $filters['idmateria'] = $request->query('idmateria');
            }
            if ($request->query('dia')) {
                $filters['dia'] = $request->query('dia');
            }

            $asignaciones = $this->asignacionModel->findAllWithHorarios($filters);

            return response()->json([
                'success' => true,
                'data' => $asignaciones
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una asignación por ID
     */
    public function show($id): JsonResponse
    {
        try {
            $asignacion = $this->asignacionModel->findById($id);

            if (!$asignacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada'
                ], 404);
            }

            // Obtener horarios de la asignación
            $horarios = $this->asignacionHorarioModel->findByAsignacion($id);
            $asignacion['horarios'] = $horarios;

            return response()->json([
                'success' => true,
                'data' => $asignacion
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva asignación con horarios
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validaciones
            $idgrupo = $request->input('idgrupo');
            $idmateria = $request->input('idmateria');
            $iddocente = $request->input('iddocente');
            $idaula = $request->input('idaula');
            $idgestion = $request->input('idgestion');
            $horarios = $request->input('horarios', []);

            if (!$idgrupo || !$idmateria || !$iddocente || !$idaula || !$idgestion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Todos los campos son requeridos: idgrupo, idmateria, iddocente, idaula, idgestion'
                ], 400);
            }

            if (empty($horarios) || !is_array($horarios)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar al menos un horario'
                ], 400);
            }

            // Validar que las entidades existan
            if (!$this->grupoModel->exists($idgrupo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El grupo especificado no existe'
                ], 400);
            }

            if (!$this->materiaModel->exists($idmateria)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La materia especificada no existe'
                ], 400);
            }

            if (!$this->docenteModel->exists($iddocente)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El docente especificado no existe'
                ], 400);
            }

            if (!$this->aulaModel->exists($idaula)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El aula especificada no existe'
                ], 400);
            }

            if (!$this->gestionModel->exists($idgestion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La gestión especificada no existe'
                ], 400);
            }

            // Validar conflictos de horario
            $conflicts = $this->asignacionModel->checkConflicts(
                $idgestion, 
                $iddocente, 
                $idaula, 
                $idgrupo, 
                $horarios
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se encontraron conflictos de horario',
                    'conflicts' => $conflicts
                ], 409);
            }

            // Crear la asignación
            $asignacion = $this->asignacionModel->create([
                'idgrupo' => $idgrupo,
                'idmateria' => $idmateria,
                'iddocente' => $iddocente,
                'idaula' => $idaula,
                'idgestion' => $idgestion
            ]);

            // Agregar horarios
            $this->asignacionHorarioModel->createMultiple($asignacion['idasignacion'], $horarios);

            // Registrar en bitácora
            $grupo = $this->grupoModel->findById($idgrupo);
            $materia = $this->materiaModel->findById($idmateria);
            
            activity('Asignaciones')
                ->withProperties([
                    'idasignacion' => $asignacion['idasignacion'],
                    'grupo' => $grupo['nombre_grupo'] ?? '',
                    'materia' => $materia['nombre'] ?? '',
                    'horarios' => count($horarios)
                ])
                ->log("Asignación creada: Grupo {$grupo['nombre_grupo']} - {$materia['nombre']}");

            return response()->json([
                'success' => true,
                'message' => 'Asignación creada exitosamente',
                'data' => [
                    'idasignacion' => $asignacion['idasignacion'],
                    'horarios' => $this->asignacionHorarioModel->findByAsignacion($asignacion['idasignacion'])
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear asignación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar horarios de una asignación existente
     */
    public function updateSlots($id, Request $request): JsonResponse
    {
        try {
            // Verificar que la asignación existe
            if (!$this->asignacionModel->exists($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada'
                ], 404);
            }

            $horarios = $request->input('slots', []);

            if (empty($horarios) || !is_array($horarios)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar al menos un horario'
                ], 400);
            }

            // Obtener datos de la asignación actual
            $asignacion = $this->asignacionModel->findById($id);

            // Validar conflictos de horario (excluyendo la asignación actual)
            $conflicts = $this->asignacionModel->checkConflicts(
                $asignacion['idgestion'],
                $asignacion['iddocente'],
                $asignacion['idaula'],
                $asignacion['idgrupo'],
                $horarios,
                $id // Excluir esta asignación de la validación
            );

            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se encontraron conflictos de horario',
                    'conflicts' => $conflicts
                ], 409);
            }

            // Actualizar horarios
            $this->asignacionHorarioModel->updateHorarios($id, $horarios);

            // Registrar en bitácora
            activity('Asignaciones')
                ->withProperties([
                    'idasignacion' => $id,
                    'horarios' => count($horarios)
                ])
                ->log("Horarios actualizados para asignación #{$id}");

            return response()->json([
                'success' => true,
                'message' => 'Horarios actualizados exitosamente',
                'data' => $this->asignacionHorarioModel->findByAsignacion($id)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar horarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una asignación
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Verificar que la asignación existe
            $asignacion = $this->asignacionModel->findById($id);
            if (!$asignacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada'
                ], 404);
            }

            // Eliminar la asignación (los horarios se eliminan automáticamente por CASCADE)
            $this->asignacionModel->delete($id);

            // Registrar en bitácora
            activity('Asignaciones')
                ->withProperties([
                    'idasignacion' => $id,
                    'grupo' => $asignacion['nombre_grupo'],
                    'materia' => $asignacion['materia_nombre']
                ])
                ->log("Asignación eliminada: Grupo {$asignacion['nombre_grupo']} - {$asignacion['materia_nombre']}");

            return response()->json([
                'success' => true,
                'message' => 'Asignación eliminada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar asignación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar conflictos antes de crear/actualizar (endpoint de utilidad)
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        try {
            $idgestion = $request->input('idgestion');
            $iddocente = $request->input('iddocente');
            $idaula = $request->input('idaula');
            $idgrupo = $request->input('idgrupo');
            $horarios = $request->input('horarios', []);
            $excludeId = $request->input('exclude_id');

            if (!$idgestion || !$iddocente || !$idaula || !$idgrupo || empty($horarios)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan parámetros requeridos'
                ], 400);
            }

            $conflicts = $this->asignacionModel->checkConflicts(
                $idgestion,
                $iddocente,
                $idaula,
                $idgrupo,
                $horarios,
                $excludeId
            );

            return response()->json([
                'success' => true,
                'has_conflicts' => !empty($conflicts),
                'conflicts' => $conflicts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar conflictos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar todas las asignaciones de una gestión
     */
    public function destroyByGestion($idgestion): JsonResponse
    {
        try {
            if (!$this->gestionModel->exists($idgestion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La gestión especificada no existe'
                ], 404);
            }

            $this->asignacionModel->deleteByGestion($idgestion);

            // Registrar en bitácora
            activity('Asignaciones')
                ->withProperties(['idgestion' => $idgestion])
                ->log("Todas las asignaciones de la gestión #{$idgestion} fueron eliminadas");

            return response()->json([
                'success' => true,
                'message' => 'Asignaciones eliminadas exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar asignaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}