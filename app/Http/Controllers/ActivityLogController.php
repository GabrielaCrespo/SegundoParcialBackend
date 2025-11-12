<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Mostrar todos los registros de la bitácora.
     * Permite filtros opcionales por usuario, log_name o fechas.
     */
  public function index(Request $request): JsonResponse
{
    try {
        $query = Activity::query();

        // 🔍 Filtros opcionales
        if ($request->has('usuario')) {
            $query->where('properties->usuario', 'ILIKE', '%' . $request->input('usuario') . '%');
        }

        if ($request->has('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        // ✅ Filtro flexible por fecha
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->input('fecha_inicio') . ' 00:00:00',
                $request->input('fecha_fin') . ' 23:59:59',
            ]);
        } elseif ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_inicio'));
        } elseif ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_fin'));
        }

        // 🔹 Obtener todos los registros
        $logs = $query->orderBy('created_at', 'desc')->get();

        // 🔹 Formatear salida
        $formatted = $logs->map(function ($log) {
            return [
                'id'          => $log->id,
                'log_name'    => $log->log_name,
                'descripcion' => $log->description,
                'usuario'     => $log->properties['usuario'] ?? 'Desconocido',
                'detalles'    => $log->properties,
                'fecha'       => $log->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener registros de la bitácora',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Mostrar un registro específico de la bitácora por ID.
     */
    public function show($id): JsonResponse
    {
        try {
            $log = Activity::find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $log->id,
                    'log_name' => $log->log_name,
                    'descripcion' => $log->description,
                    'usuario' => $log->properties['usuario'] ?? 'Desconocido',
                    'detalles' => $log->properties,
                    'fecha' => $log->created_at->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener registro de bitácora',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
