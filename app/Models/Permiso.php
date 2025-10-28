<?php

namespace App\Models;

use App\Services\DatabaseService;

class Permiso
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT idpermiso, nombre, descripcion FROM permiso ORDER BY nombre";
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT idpermiso, nombre, descripcion FROM permiso WHERE idpermiso = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function findByNombre($nombre)
    {
        $query = "SELECT idpermiso, nombre, descripcion FROM permiso WHERE nombre = $1";
        return $this->db->fetchOne($query, [$nombre]);
    }
    
    public function create($data)
    {
        $query = "INSERT INTO permiso (nombre, descripcion) 
                  VALUES ($1, $2) 
                  RETURNING idpermiso, nombre, descripcion";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['descripcion'] ?? null
        ]);
    }
    
    public function update($id, $data)
    {
        $query = "UPDATE permiso 
                  SET nombre = $1, descripcion = $2 
                  WHERE idpermiso = $3 
                  RETURNING idpermiso, nombre, descripcion";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['descripcion'] ?? null,
            $id
        ]);
    }
    
    public function delete($id)
    {
        $query = "DELETE FROM permiso WHERE idpermiso = $1";
        return $this->db->query($query, [$id]);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM permiso WHERE idpermiso = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return $result['exists'] ?? false;
    }
    
    public function getRolesByPermiso($idpermiso)
    {
        $query = "SELECT r.idrol, r.nombre, r.descripcion 
                  FROM rol r 
                  INNER JOIN rol_permiso rp ON r.idrol = rp.idrol 
                  WHERE rp.idpermiso = $1 
                  ORDER BY r.nombre";
        
        return $this->db->fetchAll($query, [$idpermiso]);
    }
}