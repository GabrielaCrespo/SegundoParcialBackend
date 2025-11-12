<?php

namespace App\Http\Controllers;

use App\Models\Gestion;
use Illuminate\Http\Request;
use Exception;
use App\Models\SessionToken; 

class GestionController extends Controller
{
    private $gestion;
    
    public function __construct()
    {
        $this->gestion = new Gestion();
    }
    
    public function index()
    {
        try {
            $gestiones = $this->gestion->findAll();
            
            return response()->json([
                'success' => true,
                'data' => $gestiones
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener gestiones: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $gestion = $this->gestion->findById($id);
            
            if (!$gestion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gestión no encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $gestion
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener gestión: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'anio' => 'required|integer|min:2020|max:2030',
                'periodo' => 'required|string|in:I,II,VERANO',
                'fechainicio' => 'required|date',
                'fechafin' => 'required|date|after:fechainicio'
            ]);
            
            $gestion = $this->gestion->create($data);

            // 🔹 Obtener usuario logueado desde session_tokens
            $ultimaSesion = SessionToken::latest('idsession')->first();
            $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

            // 🔹 Registrar en Activity Log (sin bandera)
            activity('Gestión')
                ->withProperties([
                    'usuario' => $nombreUsuario,
                    'anio' => $data['anio'],
                    'periodo' => $data['periodo'],
                    'fechainicio' => $data['fechainicio'],
                    'fechafin' => $data['fechafin']
                ])
                ->log("El usuario {$nombreUsuario} creó una nueva gestión: {$data['anio']} - {$data['periodo']}");
            
            return response()->json([
                'success' => true,
                'message' => 'Gestión creada exitosamente',
                'data' => $gestion
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear gestión: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'anio' => 'required|integer|min:2020|max:2030',
                'periodo' => 'required|string|in:I,II,VERANO',
                'fechainicio' => 'required|date',
                'fechafin' => 'required|date|after:fechainicio'
            ]);
            
            $gestion = $this->gestion->update($id, $data);
            
            if (!$gestion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gestión no encontrada'
                ], 404);
            }
            
            // 🔹 Registrar en Activity Log (con bandera para evitar duplicados)
            if (!app()->bound('activity_logged')) {
                $ultimaSesion = SessionToken::latest('idsession')->first();
                $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

                activity('Gestión')
                    ->withProperties([
                        'usuario' => $nombreUsuario,
                        'id_gestion' => $id,
                        'anio' => $data['anio'],
                        'periodo' => $data['periodo'],
                        'fechainicio' => $data['fechainicio'],
                        'fechafin' => $data['fechafin']
                    ])
                    ->log("El usuario {$nombreUsuario} actualizó la gestión con ID {$id}");

                app()->instance('activity_logged', true);
            }


            return response()->json([
                'success' => true,
                'message' => 'Gestión actualizada exitosamente',
                'data' => $gestion
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar gestión: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {

            // 🔹 Obtener datos antes de eliminar
        $gestionModel = new \App\Models\Gestion();
        $gestionEliminada = $gestionModel->findById($id);

           $deleted = $this->gestion->delete($id);

            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gestión no encontrada'
                ], 404);
            }
            
           // 🔹 Obtener usuario logueado desde session_tokens
        $ultimaSesion = \App\Models\SessionToken::latest('idsession')->first();
        $nombreUsuario = $ultimaSesion ? $ultimaSesion->nombre : 'Sistema';

        // 🔹 Registrar en Activity Log (sin modificar flujo)
        activity('Gestión')
            ->withProperties([
                'usuario' => $nombreUsuario,
                'id_gestion' => $id,
                'anio' => $gestionEliminada['anio'] ?? null,
                'periodo' => $gestionEliminada['periodo'] ?? null,
                'fechainicio' => $gestionEliminada['fechainicio'] ?? null,
                'fechafin' => $gestionEliminada['fechafin'] ?? null,
            ])
            ->log(
                "El usuario {$nombreUsuario} eliminó la gestión " .
                ($gestionEliminada['anio'] ?? 'desconocida') . ' - ' .
                ($gestionEliminada['periodo'] ?? '') .
                " (ID {$id})"
            );

            return response()->json([
                'success' => true,
                'message' => 'Gestión eliminada exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar gestión: ' . $e->getMessage()
            ], 500);
        }
    }
}