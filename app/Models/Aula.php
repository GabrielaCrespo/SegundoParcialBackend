<?php

namespace App\Models;

use App\Services\DatabaseService;

class Aula
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT a.idaula, a.numero, a.tipo, a.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM aula a
                  LEFT JOIN facultad f ON a.idfacultad = f.idfacultad
                  ORDER BY a.numero";
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT a.idaula, a.numero, a.tipo, a.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM aula a
                  LEFT JOIN facultad f ON a.idfacultad = f.idfacultad
                  WHERE a.idaula = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function findByNumero($numero)
    {
        $query = "SELECT a.idaula, a.numero, a.tipo, a.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM aula a
                  LEFT JOIN facultad f ON a.idfacultad = f.idfacultad
                  WHERE a.numero = $1";
        return $this->db->fetchOne($query, [$numero]);
    }
    
    public function create($data)
    {
        $query = "INSERT INTO aula (numero, tipo, idfacultad) 
                  VALUES ($1, $2, $3) 
                  RETURNING idaula, numero, tipo, idfacultad";
        
        return $this->db->fetchOne($query, [
            $data['numero'],
            $data['tipo'],
            $data['idfacultad']
        ]);
    }
    
    public function update($id, $data)
    {
        $query = "UPDATE aula 
                  SET numero = $1, tipo = $2, idfacultad = $3 
                  WHERE idaula = $4 
                  RETURNING idaula, numero, tipo, idfacultad";
        
        return $this->db->fetchOne($query, [
            $data['numero'],
            $data['tipo'],
            $data['idfacultad'],
            $id
        ]);
    }
    
    public function delete($id)
    {
        $query = "DELETE FROM aula WHERE idaula = $1";
        return $this->db->query($query, [$id]);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM aula WHERE idaula = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return ($result['exists'] ?? 'f') === 't';
    }
    
    public function findByFacultad($idfacultad)
    {
        $query = "SELECT a.idaula, a.numero, a.tipo, a.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM aula a
                  LEFT JOIN facultad f ON a.idfacultad = f.idfacultad
                  WHERE a.idfacultad = $1
                  ORDER BY a.numero";
        return $this->db->fetchAll($query, [$idfacultad]);
    }
    
    public function findByTipo($tipo)
    {
        $query = "SELECT a.idaula, a.numero, a.tipo, a.idfacultad,
                         f.nombre as facultad_nombre, f.nro as facultad_nro
                  FROM aula a
                  LEFT JOIN facultad f ON a.idfacultad = f.idfacultad
                  WHERE a.tipo ILIKE $1
                  ORDER BY a.numero";
        return $this->db->fetchAll($query, ['%' . $tipo . '%']);
    }
    
    public function isNumeroUnique($numero, $excludeId = null)
    {
        if ($excludeId) {
            $query = "SELECT EXISTS(SELECT 1 FROM aula WHERE numero = $1 AND idaula != $2) as exists";
            $result = $this->db->fetchOne($query, [$numero, $excludeId]);
        } else {
            $query = "SELECT EXISTS(SELECT 1 FROM aula WHERE numero = $1) as exists";
            $result = $this->db->fetchOne($query, [$numero]);
        }
        
        return ($result['exists'] ?? 'f') === 't';
    }
}