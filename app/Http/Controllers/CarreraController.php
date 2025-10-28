<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\Request;
use Exception;

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
            $deleted = $this->carrera->delete($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Carrera no encontrada'
                ], 404);
            }
            
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