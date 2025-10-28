<?php

namespace App\Models;

use App\Services\DatabaseService;

class Gestion
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT idgestion, anio, periodo, fechainicio, fechafin 
                  FROM gestion 
                  ORDER BY anio DESC, periodo";
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT idgestion, anio, periodo, fechainicio, fechafin 
                  FROM gestion 
                  WHERE idgestion = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function findByAnioYPeriodo($anio, $periodo)
    {
        $query = "SELECT idgestion, anio, periodo, fechainicio, fechafin 
                  FROM gestion 
                  WHERE anio = $1 AND periodo = $2";
        return $this->db->fetchOne($query, [$anio, $periodo]);
    }
    
    public function create($data)
    {
        $query = "INSERT INTO gestion (anio, periodo, fechainicio, fechafin) 
                  VALUES ($1, $2, $3, $4) 
                  RETURNING idgestion, anio, periodo, fechainicio, fechafin";
        
        return $this->db->fetchOne($query, [
            $data['anio'],
            $data['periodo'],
            $data['fechainicio'],
            $data['fechafin']
        ]);
    }
    
    public function update($id, $data)
    {
        $query = "UPDATE gestion 
                  SET anio = $1, periodo = $2, fechainicio = $3, fechafin = $4 
                  WHERE idgestion = $5 
                  RETURNING idgestion, anio, periodo, fechainicio, fechafin";
        
        return $this->db->fetchOne($query, [
            $data['anio'],
            $data['periodo'],
            $data['fechainicio'],
            $data['fechafin'],
            $id
        ]);
    }
    
    public function delete($id)
    {
        $query = "DELETE FROM gestion WHERE idgestion = $1";
        return $this->db->query($query, [$id]);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM gestion WHERE idgestion = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return $result['exists'] ?? false;
    }
    
    public function findByAnio($anio)
    {
        $query = "SELECT idgestion, anio, periodo, fechainicio, fechafin 
                  FROM gestion 
                  WHERE anio = $1 
                  ORDER BY periodo";
        return $this->db->fetchAll($query, [$anio]);
    }
    
    public function getCurrentGestion()
    {
        $query = "SELECT idgestion, anio, periodo, fechainicio, fechafin 
                  FROM gestion 
                  WHERE CURRENT_DATE BETWEEN fechainicio AND fechafin 
                  LIMIT 1";
        return $this->db->fetchOne($query);
    }
    
    public function validateFechas($fechainicio, $fechafin)
    {
        return strtotime($fechafin) >= strtotime($fechainicio);
    }
    
    public function validateAnio($anio)
    {
        return $anio >= 2000 && $anio <= 2100;
    }
}