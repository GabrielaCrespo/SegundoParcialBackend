<?php

namespace App\Http\Controllers;

use App\Models\Gestion;
use Illuminate\Http\Request;
use Exception;

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
            $deleted = $this->gestion->delete($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gestión no encontrada'
                ], 404);
            }
            
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