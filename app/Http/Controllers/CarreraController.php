<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\Request;
use Exception;
use App\Models\SessionToken;


class CarreraController extends Controller
{
    private $carrera;

    public function __construct()
    {
        $this->carrera = new Carrera();
    }

    public function index()
    {
        try {
            $carreras = $this->carrera->findAll();

            return response()->json([
                'success' => true,
                'data' => $carreras
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener carreras: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $carrera = $this->carrera->findById($id);

            if (!$carrera) {
                return response()->json([
                    'success' => false,
                    'message' => 'Carrera no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $carrera
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener carrera: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:255',
                'sigla' => 'required|string|max:50',
                'idfacultad' => 'required|integer'
            ]);

            $carrera = $this->carrera->create($data);

            /** 🔹 Registrar en Activity Log (sin bandera, creación no se duplica) */
            $ultimaSesion = SessionToken::latest('idsession')->first();
            $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

            activity('Carrera')
                ->withProperties([
                    'usuario' => $nombreUsuario,
                    'nombre_carrera' => $data['nombre'],
                    'sigla' => $data['sigla'],
                    'idfacultad' => $data['idfacultad']
                ])
                ->log("El usuario {$nombreUsuario} creó una nueva carrera: {$data['nombre']}");

            return response()->json([
                'success' => true,
                'message' => 'Carrera creada exitosamente',
                'data' => $carrera
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear carrera: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:255',
                'sigla' => 'required|string|max:50',
                'idfacultad' => 'required|integer'
            ]);

            $carrera = $this->carrera->update($id, $data);

            if (!$carrera) {
                return response()->json([
                    'success' => false,
                    'message' => 'Carrera no encontrada'
                ], 404);
            }

            /** 🔹 NUEVO BLOQUE AÑADIDO (para registrar en Activity Log una sola vez) */
            if (!app()->bound('activity_logged')) {
                // Obtener el usuario logueado
                $ultimaSesion = SessionToken::latest('idsession')->first();
                $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

                // Registrar en Activity Log
                activity('Carrera')
                    ->withProperties([
                        'usuario' => $nombreUsuario,
                        'id_carrera' => $id,
                        'nombre' => $data['nombre'],
                        'sigla' => $data['sigla'],
                        'idfacultad' => $data['idfacultad']
                    ])
                    ->log("El usuario {$nombreUsuario} actualizó la carrera con ID {$id}");

                // Bandera para evitar doble registro
                app()->instance('activity_logged', true);
            }

            return response()->json([
                'success' => true,
                'message' => 'Carrera actualizada exitosamente',
                'data' => $carrera
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar carrera: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {

            $carreraModel = new \App\Models\Carrera();
            $carreraEliminada = $carreraModel->findById($id);
            $deleted = $this->carrera->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Carrera no encontrada'
                ], 404);
            }

            // 🔹 Obtener usuario logueado desde session_tokens
            $ultimaSesion = \App\Models\SessionToken::latest('idsession')->first();
            $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

            // 🔹 Registrar en Activity Log (ahora con datos reales de la carrera eliminada)
            activity('Carrera')
                ->withProperties([
                    'usuario' => $nombreUsuario,
                    'id_carrera' => $id,
                    'nombre_carrera' => $carreraEliminada['nombre'] ?? 'Desconocida',
                    'sigla' => $carreraEliminada['sigla'] ?? null,
                    'idfacultad' => $carreraEliminada['idfacultad'] ?? null,
                ])
                ->log("El usuario {$nombreUsuario} eliminó la carrera '" . ($carreraEliminada['nombre'] ?? 'desconocida') . "' (ID {$id})");

            return response()->json([
                'success' => true,
                'message' => 'Carrera eliminada exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar carrera: ' . $e->getMessage()
            ], 500);
        }
    }
}
