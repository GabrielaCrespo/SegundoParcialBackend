<?php

namespace App\Models;

use App\Services\DatabaseService;

class Facultad
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT idfacultad, nro, nombre 
                  FROM facultad 
                  ORDER BY nombre";
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT idfacultad, nro, nombre 
                  FROM facultad 
                  WHERE idfacultad = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function findByNro($nro)
    {
        $query = "SELECT idfacultad, nro, nombre 
                  FROM facultad 
                  WHERE nro = $1";
        return $this->db->fetchOne($query, [$nro]);
    }
    
    public function findByNombre($nombre)
    {
        $query = "SELECT idfacultad, nro, nombre 
                  FROM facultad 
                  WHERE nombre ILIKE $1";
        return $this->db->fetchAll($query, ['%' . $nombre . '%']);
    }
    
    public function create($data)
    {
        $query = "INSERT INTO facultad (nro, nombre) 
                  VALUES ($1, $2) 
                  RETURNING idfacultad, nro, nombre";
        
        return $this->db->fetchOne($query, [
            $data['nro'],
            $data['nombre']
        ]);
    }
    
    public function update($id, $data)
    {
        $query = "UPDATE facultad 
                  SET nro = $1, nombre = $2 
                  WHERE idfacultad = $3 
                  RETURNING idfacultad, nro, nombre";
        
        return $this->db->fetchOne($query, [
            $data['nro'],
            $data['nombre'],
            $id
        ]);
    }
    
    public function delete($id)
    {
        $query = "DELETE FROM facultad WHERE idfacultad = $1";
        return $this->db->query($query, [$id]);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM facultad WHERE idfacultad = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return ($result['exists'] ?? 'f') === 't';
    }
    
    public function isNroUnique($nro, $excludeId = null)
    {
        if ($excludeId) {
            $query = "SELECT EXISTS(SELECT 1 FROM facultad WHERE nro = $1 AND idfacultad != $2) as exists";
            $result = $this->db->fetchOne($query, [$nro, $excludeId]);
        } else {
            $query = "SELECT EXISTS(SELECT 1 FROM facultad WHERE nro = $1) as exists";
            $result = $this->db->fetchOne($query, [$nro]);
        }
        
        return ($result['exists'] ?? 'f') === 't';
    }
    
    public function getAulas($id)
    {
        $query = "SELECT idaula, numero, tipo 
                  FROM aula 
                  WHERE idfacultad = $1 
                  ORDER BY numero";
        return $this->db->fetchAll($query, [$id]);
    }
    
    public function getCarreras($id)
    {
        $query = "SELECT idcarrera, nombre, sigla 
                  FROM carrera 
                  WHERE idfacultad = $1 
                  ORDER BY nombre";
        return $this->db->fetchAll($query, [$id]);
    }
    
    public function getStatsAulas($id)
    {
        $query = "SELECT 
                    COUNT(*) as total_aulas,
                    COUNT(CASE WHEN tipo = 'Aula' THEN 1 END) as aulas_normales,
                    COUNT(CASE WHEN tipo = 'Laboratorio' THEN 1 END) as laboratorios,
                    COUNT(CASE WHEN tipo = 'Auditorio' THEN 1 END) as auditorios
                  FROM aula 
                  WHERE idfacultad = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function getStatsCarreras($id)
    {
        $query = "SELECT COUNT(*) as total_carreras
                  FROM carrera 
                  WHERE idfacultad = $1";
        return $this->db->fetchOne($query, [$id]);
    }
}