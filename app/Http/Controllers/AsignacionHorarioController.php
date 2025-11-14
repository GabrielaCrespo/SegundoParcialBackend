<?php

namespace App\Http\Controllers;

use App\Models\AsignacionHorario;
use App\Models\Asignacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AsignacionHorarioController extends Controller
{
    private $asignacionHorarioModel;
    private $asignacionModel;

    public function __construct()
    {
        $this->asignacionHorarioModel = new AsignacionHorario();
        $this->asignacionModel = new Asignacion();
    }

    /**
     * Obtener todos los horarios de una asignación
     */
    public function getHorariosByAsignacion($idasignacion): JsonResponse
    {
        try {
            if (!$this->asignacionModel->exists($idasignacion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada'
                ], 404);
            }

            $horarios = $this->asignacionHorarioModel->findByAsignacion($idasignacion);

            return response()->json([
                'success' => true,
                'data' => $horarios
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar un horario a una asignación
     */
    public function addHorario(Request $request): JsonResponse
    {
        try {
            $idasignacion = $request->input('idasignacion');
            $idhorario = $request->input('idhorario');

            if (!$idasignacion || !$idhorario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los campos idasignacion e idhorario son requeridos'
                ], 400);
            }

            if (!$this->asignacionModel->exists($idasignacion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada'
                ], 404);
            }

            // Verificar si ya existe
            if ($this->asignacionHorarioModel->exists($idasignacion, $idhorario)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El horario ya está asignado'
                ], 409);
            }

            $result = $this->asignacionHorarioModel->create($idasignacion, $idhorario);

            return response()->json([
                'success' => true,
                'message' => 'Horario agregado exitosamente',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un horario de una asignación
     */
    public function removeHorario($idasignacion, $idhorario): JsonResponse
    {
        try {
            if (!$this->asignacionModel->exists($idasignacion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asignación no encontrada'
                ], 404);
            }

            $this->asignacionHorarioModel->delete($idasignacion, $idhorario);

            return response()->json([
                'success' => true,
                'message' => 'Horario eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}