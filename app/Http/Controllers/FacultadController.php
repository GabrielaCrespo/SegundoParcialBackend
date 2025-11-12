<?php

namespace App\Http\Controllers;

use App\Models\Facultad;
use Illuminate\Http\Request;
use Exception;
use App\Models\SessionToken; // 👈 nuevo


class FacultadController extends Controller
{
    private $facultad;

    public function __construct()
    {
        $this->facultad = new Facultad();
    }

    public function index()
    {
        try {
            $facultades = $this->facultad->findAll();

            return response()->json([
                'success' => true,
                'data' => $facultades
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facultades: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $facultad = $this->facultad->findById($id);

            if (!$facultad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facultad no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $facultad
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facultad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nro' => 'required|integer|min:1',
                'nombre' => 'required|string|max:255'
            ]);

            $facultad = $this->facultad->create($data);

            // 🔹 Obtener usuario logueado desde session_tokens
            $ultimaSesion = SessionToken::latest('idsession')->first();
            $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

            // 🔹 Registrar en Activity Log
            activity('Facultad')
                ->withProperties([
                    'usuario' => $nombreUsuario,
                    'nombre_facultad' => $data['nombre'],
                    'nro' => $data['nro']
                ])
                ->log("El usuario {$nombreUsuario} creó una nueva facultad: {$data['nombre']}");


            return response()->json([
                'success' => true,
                'message' => 'Facultad creada exitosamente',
                'data' => $facultad
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear facultad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'nro' => 'required|integer|min:1',
                'nombre' => 'required|string|max:255'
            ]);

            $facultad = $this->facultad->update($id, $data);

            if (!$facultad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facultad no encontrada'
                ], 404);
            }

            // 🔹 Evitar que se registre dos veces en la misma ejecución
            if (!app()->bound('activity_logged')) {

                // 🔹 Obtener usuario logueado desde session_tokens
                $ultimaSesion = \App\Models\SessionToken::latest('idsession')->first();
                $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

                // 🔹 Registrar en Activity Log una sola vez
                activity('Facultad')
                    ->withProperties([
                        'usuario' => $nombreUsuario,
                        'id_facultad' => $id,
                    ])
                    ->log("El usuario {$nombreUsuario} actualizó la facultad con ID {$id}");

                // 🔒 Bandera interna para que no se vuelva a registrar en esta request
                app()->instance('activity_logged', true);
            }


            return response()->json([
                'success' => true,
                'message' => 'Facultad actualizada exitosamente',
                'data' => $facultad
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar facultad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->facultad->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facultad no encontrada'
                ], 404);
            }

            // 🔹 Obtener usuario logueado desde session_tokens
            $ultimaSesion = SessionToken::latest('idsession')->first();
            $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

            // 🔹 Registrar en Activity Log
            activity('Facultad')
                ->withProperties([
                    'usuario' => $nombreUsuario,
                    'id_facultad' => $id
                ])
                ->log("El usuario {$nombreUsuario} eliminó la facultad con ID {$id}");


            return response()->json([
                'success' => true,
                'message' => 'Facultad eliminada exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar facultad: ' . $e->getMessage()
            ], 500);
        }
    }
}
