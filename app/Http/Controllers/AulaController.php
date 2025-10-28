<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use Illuminate\Http\Request;
use Exception;

class AulaController extends Controller
{
    private $aula;
    
    public function __construct()
    {
        $this->aula = new Aula();
    }
    
    public function index()
    {
        try {
            $aulas = $this->aula->findAll();
            
            return response()->json([
                'success' => true,
                'data' => $aulas
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener aulas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $aula = $this->aula->findById($id);
            
            if (!$aula) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aula no encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $aula
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener aula: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'numero' => 'required|string|max:50',
                'tipo' => 'required|string|max:100',
                'idfacultad' => 'required|integer'
            ]);
            
            $aula = $this->aula->create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Aula creada exitosamente',
                'data' => $aula
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear aula: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'numero' => 'required|string|max:50',
                'tipo' => 'required|string|max:100',
                'idfacultad' => 'required|integer'
            ]);
            
            $aula = $this->aula->update($id, $data);
            
            if (!$aula) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aula no encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Aula actualizada exitosamente',
                'data' => $aula
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar aula: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $deleted = $this->aula->delete($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aula no encontrada'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Aula eliminada exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar aula: ' . $e->getMessage()
            ], 500);
        }
    }
}