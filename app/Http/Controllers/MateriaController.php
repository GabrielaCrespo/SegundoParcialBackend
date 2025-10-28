<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Exception;

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
            $deleted = $this->materia->delete($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materia no encontrada'
                ], 404);
            }
            
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