<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;
use Exception;

class HorarioController extends Controller
{
    private $horario;
    
    public function __construct()
    {
        $this->horario = new Horario();
    }
    
    public function index()
    {
        try {
            $horarios = $this->horario->findAll();
            
            return response()->json([
                'success' => true,
                'data' => $horarios
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $horario = $this->horario->findById($id);
            
            if (!$horario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horario no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $horario
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horario: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'dia' => 'required|string|in:LU,MA,MI,JU,VI,SA',
                'horainicio' => 'required|string',
                'horafinal' => 'required|string'
            ]);
            
            $horario = $this->horario->create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Horario creado exitosamente',
                'data' => $horario
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear horario: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'dia' => 'required|string|in:LU,MA,MI,JU,VI,SA',
                'horainicio' => 'required|string',
                'horafinal' => 'required|string'
            ]);
            
            $horario = $this->horario->update($id, $data);
            
            if (!$horario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horario no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Horario actualizado exitosamente',
                'data' => $horario
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar horario: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $deleted = $this->horario->delete($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horario no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Horario eliminado exitosamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar horario: ' . $e->getMessage()
            ], 500);
        }
    }
}