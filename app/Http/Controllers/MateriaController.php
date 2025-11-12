<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Exception;
use App\Models\SessionToken;

class MateriaController extends Controller
{
    private $materia;

    public function __construct()
    {
        $this->materia = new Materia();
    }

    public function index()
    {
        try {
            $materias = $this->materia->findAll();

            return response()->json([
                'success' => true,
                'data' => $materias
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener materias: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $materia = $this->materia->findById($id);

            if (!$materia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materia no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $materia
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener materia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:255',
                'sigla' => 'required|string|max:50',
                'semestre' => 'required|integer|min:1|max:10',
                'idgestion' => 'required|integer'
            ]);

            $materia = $this->materia->create($data);

            /** 🔹 Registrar en Activity Log (sin bandera, creación no se duplica) */
            $ultimaSesion = SessionToken::latest('idsession')->first();
            $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

            activity('Materia')
                ->withProperties([
                    'usuario' => $nombreUsuario,
                    'nombre_materia' => $data['nombre'],
                    'sigla' => $data['sigla'],
                    'semestre' => $data['semestre'],
                    'idgestion' => $data['idgestion']
                ])
                ->log("El usuario {$nombreUsuario} creó una nueva materia: {$data['nombre']}");

            return response()->json([
                'success' => true,
                'message' => 'Materia creada exitosamente',
                'data' => $materia
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear materia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:255',
                'sigla' => 'required|string|max:50',
                'semestre' => 'required|integer|min:1|max:10',
                'idgestion' => 'required|integer'
            ]);

            $materia = $this->materia->update($id, $data);

            if (!$materia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materia no encontrada'
                ], 404);
            }

            /** 🔹 Registrar en Activity Log (con bandera para evitar duplicados) */
            if (!app()->bound('activity_logged')) {
                $ultimaSesion = SessionToken::latest('idsession')->first();
                $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

                activity('Materia')
                    ->withProperties([
                        'usuario' => $nombreUsuario,
                        'id_materia' => $id,
                        'nombre' => $data['nombre'],
                        'sigla' => $data['sigla'],
                        'semestre' => $data['semestre'],
                        'idgestion' => $data['idgestion']
                    ])
                    ->log("El usuario {$nombreUsuario} actualizó la materia con ID {$id}");

                app()->instance('activity_logged', true);
            }

            return response()->json([
                'success' => true,
                'message' => 'Materia actualizada exitosamente',
                'data' => $materia
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar materia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {

            // 🔹 Obtener los datos de la materia antes de eliminarla
            $materiaModel = new \App\Models\Materia();
            $materiaEliminada = $materiaModel->findById($id);
            $deleted = $this->materia->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materia no encontrada'
                ], 404);
            }

            // 🔹 Obtener usuario logueado desde session_tokens
            $ultimaSesion = \App\Models\SessionToken::latest('idsession')->first();
            $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

            // 🔹 Registrar en Activity Log (sin bandera)
            activity('Materia')
                ->withProperties([
                    'usuario' => $nombreUsuario,
                    'id_materia' => $id,
                    'nombre_materia' => $materiaEliminada['nombre'] ?? 'Desconocida',
                    'sigla' => $materiaEliminada['sigla'] ?? null,
                    'semestre' => $materiaEliminada['semestre'] ?? null,
                    'idgestion' => $materiaEliminada['idgestion'] ?? null
                ])
                ->log("El usuario {$nombreUsuario} eliminó la materia '" . ($materiaEliminada['nombre'] ?? 'desconocida') . "' (ID {$id})");

            return response()->json([
                'success' => true,
                'message' => 'Materia eliminada exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar materia: ' . $e->getMessage()
            ], 500);
        }
    }
}
