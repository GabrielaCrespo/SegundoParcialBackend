<?php

namespace App\Models;

use App\Services\DatabaseService;

class Materia
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT m.idmateria, m.nombre, m.sigla, m.semestre, m.idgestion,
                         g.anio, g.periodo
                  FROM materia m
                  LEFT JOIN gestion g ON m.idgestion = g.idgestion
                  ORDER BY m.nombre";
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT m.idmateria, m.nombre, m.sigla, m.semestre, m.idgestion,
                         g.anio, g.periodo
                  FROM materia m
                  LEFT JOIN gestion g ON m.idgestion = g.idgestion
                  WHERE m.idmateria = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function findBySiglaYGestion($sigla, $idgestion)
    {
        $query = "SELECT m.idmateria, m.nombre, m.sigla, m.semestre, m.idgestion,
                         g.anio, g.periodo
                  FROM materia m
                  LEFT JOIN gestion g ON m.idgestion = g.idgestion
                  WHERE m.sigla = $1 AND m.idgestion = $2";
        return $this->db->fetchOne($query, [$sigla, $idgestion]);
    }
    
    public function create($data)
    {
        $query = "INSERT INTO materia (nombre, sigla, semestre, idgestion) 
                  VALUES ($1, $2, $3, $4) 
                  RETURNING idmateria, nombre, sigla, semestre, idgestion";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['sigla'],
            $data['semestre'] ?? 1,
            $data['idgestion']
        ]);
    }
    
    public function update($id, $data)
    {
        $query = "UPDATE materia 
                  SET nombre = $1, sigla = $2, semestre = $3, idgestion = $4 
                  WHERE idmateria = $5 
                  RETURNING idmateria, nombre, sigla, semestre, idgestion";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['sigla'],
            $data['semestre'] ?? 1,
            $data['idgestion'],
            $id
        ]);
    }
    
    public function delete($id)
    {
        $query = "DELETE FROM materia WHERE idmateria = $1";
        return $this->db->query($query, [$id]);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM materia WHERE idmateria = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return $result['exists'] ?? false;
    }
    
    public function findByGestion($idgestion)
    {
        $query = "SELECT m.idmateria, m.nombre, m.sigla, m.semestre, m.idgestion,
                         g.anio, g.periodo
                  FROM materia m
                  LEFT JOIN gestion g ON m.idgestion = g.idgestion
                  WHERE m.idgestion = $1
                  ORDER BY m.semestre, m.nombre";
        return $this->db->fetchAll($query, [$idgestion]);
    }
    
    public function findBySemestre($semestre)
    {
        $query = "SELECT m.idmateria, m.nombre, m.sigla, m.semestre, m.idgestion,
                         g.anio, g.periodo
                  FROM materia m
                  LEFT JOIN gestion g ON m.idgestion = g.idgestion
                  WHERE m.semestre = $1
                  ORDER BY m.nombre";
        return $this->db->fetchAll($query, [$semestre]);
    }
    
    public function getCarrerasByMateria($idmateria)
    {
        $query = "SELECT c.idcarrera, c.nombre, c.sigla, c.idfacultad,
                         f.nombre as facultad_nombre
                  FROM carrera c
                  INNER JOIN materia_carrera mc ON c.idcarrera = mc.idcarrera
                  LEFT JOIN facultad f ON c.idfacultad = f.idfacultad
                  WHERE mc.idmateria = $1
                  ORDER BY c.nombre";
        return $this->db->fetchAll($query, [$idmateria]);
    }
    
    public function assignToCarrera($idmateria, $idcarrera)
    {
        $query = "INSERT INTO materia_carrera (idmateria, idcarrera) VALUES ($1, $2)";
        return $this->db->query($query, [$idmateria, $idcarrera]);
    }
    
    public function removeFromCarrera($idmateria, $idcarrera)
    {
        $query = "DELETE FROM materia_carrera WHERE idmateria = $1 AND idcarrera = $2";
        return $this->db->query($query, [$idmateria, $idcarrera]);
    }
}