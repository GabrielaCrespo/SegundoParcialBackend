<?php

namespace App\Models;

use App\Services\DatabaseService;

class Carrera
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT c.idcarrera, c.nombre, c.sigla, c.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM carrera c
                  LEFT JOIN facultad f ON c.idfacultad = f.idfacultad
                  ORDER BY c.nombre";
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT c.idcarrera, c.nombre, c.sigla, c.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM carrera c
                  LEFT JOIN facultad f ON c.idfacultad = f.idfacultad
                  WHERE c.idcarrera = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function findBySigla($sigla)
    {
        $query = "SELECT c.idcarrera, c.nombre, c.sigla, c.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM carrera c
                  LEFT JOIN facultad f ON c.idfacultad = f.idfacultad
                  WHERE c.sigla = $1";
        return $this->db->fetchOne($query, [$sigla]);
    }
    
    public function create($data)
    {
        $query = "INSERT INTO carrera (nombre, sigla, idfacultad) 
                  VALUES ($1, $2, $3) 
                  RETURNING idcarrera, nombre, sigla, idfacultad";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['sigla'],
            $data['idfacultad'] ?? null
        ]);
    }
    
    public function update($id, $data)
    {
        $query = "UPDATE carrera 
                  SET nombre = $1, sigla = $2, idfacultad = $3 
                  WHERE idcarrera = $4 
                  RETURNING idcarrera, nombre, sigla, idfacultad";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['sigla'],
            $data['idfacultad'] ?? null,
            $id
        ]);
    }
    
    public function delete($id)
    {
        $query = "DELETE FROM carrera WHERE idcarrera = $1";
        return $this->db->query($query, [$id]);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM carrera WHERE idcarrera = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return ($result['exists'] ?? 'f') === 't';
    }
    
    public function findByFacultad($idfacultad)
    {
        $query = "SELECT c.idcarrera, c.nombre, c.sigla, c.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM carrera c
                  LEFT JOIN facultad f ON c.idfacultad = f.idfacultad
                  WHERE c.idfacultad = $1
                  ORDER BY c.nombre";
        return $this->db->fetchAll($query, [$idfacultad]);
    }
    
    public function findByNombre($nombre)
    {
        $query = "SELECT c.idcarrera, c.nombre, c.sigla, c.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM carrera c
                  LEFT JOIN facultad f ON c.idfacultad = f.idfacultad
                  WHERE c.nombre ILIKE $1
                  ORDER BY c.nombre";
        return $this->db->fetchAll($query, ['%' . $nombre . '%']);
    }
    
    public function isSiglaUnique($sigla, $excludeId = null)
    {
        if ($excludeId) {
            $query = "SELECT EXISTS(SELECT 1 FROM carrera WHERE sigla = $1 AND idcarrera != $2) as exists";
            $result = $this->db->fetchOne($query, [$sigla, $excludeId]);
        } else {
            $query = "SELECT EXISTS(SELECT 1 FROM carrera WHERE sigla = $1) as exists";
            $result = $this->db->fetchOne($query, [$sigla]);
        }
        
        return ($result['exists'] ?? 'f') === 't';
    }
    
    public function getMaterias($id)
    {
        $query = "SELECT m.idmateria, m.nombre, m.sigla, m.semestre,
                         g.anio, g.periodo
                  FROM materia m
                  INNER JOIN materia_carrera mc ON m.idmateria = mc.idmateria
                  LEFT JOIN gestion g ON m.idgestion = g.idgestion
                  WHERE mc.idcarrera = $1
                  ORDER BY m.semestre, m.nombre";
        return $this->db->fetchAll($query, [$id]);
    }
    
    public function addMateria($idcarrera, $idmateria)
    {
        $query = "INSERT INTO materia_carrera (idmateria, idcarrera) VALUES ($1, $2)";
        return $this->db->query($query, [$idmateria, $idcarrera]);
    }
    
    public function removeMateria($idcarrera, $idmateria)
    {
        $query = "DELETE FROM materia_carrera WHERE idmateria = $1 AND idcarrera = $2";
        return $this->db->query($query, [$idmateria, $idcarrera]);
    }
    
    public function hasMateriaAssigned($idcarrera, $idmateria)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM materia_carrera WHERE idcarrera = $1 AND idmateria = $2) as exists";
        $result = $this->db->fetchOne($query, [$idcarrera, $idmateria]);
        return ($result['exists'] ?? 'f') === 't';
    }
    
    public function getStatsMaterias($id)
    {
        $query = "SELECT 
                    COUNT(*) as total_materias,
                    COUNT(CASE WHEN m.semestre = 1 THEN 1 END) as primer_semestre,
                    COUNT(CASE WHEN m.semestre = 2 THEN 1 END) as segundo_semestre,
                    COUNT(CASE WHEN m.semestre > 2 THEN 1 END) as otros_semestres
                  FROM materia m
                  INNER JOIN materia_carrera mc ON m.idmateria = mc.idmateria
                  WHERE mc.idcarrera = $1";
        return $this->db->fetchOne($query, [$id]);
    }
}