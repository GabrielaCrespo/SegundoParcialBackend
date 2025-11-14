<?php

namespace App\Models;

use App\Services\DatabaseService;

class AsignacionHorario
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    /**
     * Obtener todos los horarios de una asignación
     */
    public function findByAsignacion($idasignacion)
    {
        $query = "SELECT ah.idasignacionhorario, ah.idasignacion, ah.idhorario,
                         h.dia, h.horainicio, h.horafinal
                  FROM asignacion_horario ah
                  INNER JOIN horario h ON ah.idhorario = h.idhorario
                  WHERE ah.idasignacion = $1
                  ORDER BY CASE h.dia 
                             WHEN 'LU' THEN 1 
                             WHEN 'MA' THEN 2 
                             WHEN 'MI' THEN 3 
                             WHEN 'JU' THEN 4 
                             WHEN 'VI' THEN 5 
                             WHEN 'SA' THEN 6 
                           END, h.horainicio";
        
        return $this->db->fetchAll($query, [$idasignacion]);
    }
    
    /**
     * Agregar un horario a una asignación
     */
    public function create($idasignacion, $idhorario)
    {
        $query = "INSERT INTO asignacion_horario (idasignacion, idhorario) 
                  VALUES ($1, $2) 
                  RETURNING idasignacionhorario, idasignacion, idhorario";
        
        return $this->db->fetchOne($query, [$idasignacion, $idhorario]);
    }
    
    /**
     * Agregar múltiples horarios a una asignación
     */
    public function createMultiple($idasignacion, $horarios)
    {
        $created = [];
        foreach ($horarios as $idhorario) {
            try {
                $created[] = $this->create($idasignacion, $idhorario);
            } catch (\Exception $e) {
                // Si ya existe, continuar con el siguiente
                if (strpos($e->getMessage(), 'duplicate') === false && 
                    strpos($e->getMessage(), 'unique') === false) {
                    throw $e;
                }
            }
        }
        return $created;
    }
    
    /**
     * Eliminar un horario de una asignación
     */
    public function delete($idasignacion, $idhorario)
    {
        $query = "DELETE FROM asignacion_horario WHERE idasignacion = $1 AND idhorario = $2";
        return $this->db->query($query, [$idasignacion, $idhorario]);
    }
    
    /**
     * Eliminar todos los horarios de una asignación
     */
    public function deleteByAsignacion($idasignacion)
    {
        $query = "DELETE FROM asignacion_horario WHERE idasignacion = $1";
        return $this->db->query($query, [$idasignacion]);
    }
    
    /**
     * Actualizar horarios de una asignación (elimina los anteriores y agrega los nuevos)
     */
    public function updateHorarios($idasignacion, $horarios)
    {
        // Eliminar horarios existentes
        $this->deleteByAsignacion($idasignacion);
        
        // Agregar nuevos horarios
        return $this->createMultiple($idasignacion, $horarios);
    }
    
    /**
     * Verificar si un horario ya está asignado
     */
    public function exists($idasignacion, $idhorario)
    {
        $query = "SELECT EXISTS(
                    SELECT 1 FROM asignacion_horario 
                    WHERE idasignacion = $1 AND idhorario = $2
                  ) as exists";
        $result = $this->db->fetchOne($query, [$idasignacion, $idhorario]);
        return ($result['exists'] ?? 'f') === 't';
    }
}