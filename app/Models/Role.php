<?php

namespace App\Models;

use App\Services\DatabaseService;

class Role
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT idrol, nombre, descripcion FROM rol ORDER BY nombre";
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT idrol, nombre, descripcion FROM rol WHERE idrol = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function findByNombre($nombre)
    {
        $query = "SELECT idrol, nombre, descripcion FROM rol WHERE nombre = $1";
        return $this->db->fetchOne($query, [$nombre]);
    }
    
    public function create($data)
    {
        $query = "INSERT INTO rol (nombre, descripcion) 
                  VALUES ($1, $2) 
                  RETURNING idrol, nombre, descripcion";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['descripcion'] ?? null
        ]);
    }
    
    public function update($id, $data)
    {
        $query = "UPDATE rol 
                  SET nombre = $1, descripcion = $2 
                  WHERE idrol = $3 
                  RETURNING idrol, nombre, descripcion";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['descripcion'] ?? null,
            $id
        ]);
    }
    
    public function delete($id)
    {
        $query = "DELETE FROM rol WHERE idrol = $1";
        return $this->db->query($query, [$id]);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM rol WHERE idrol = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return $result['exists'] ?? false;
    }
    
    public function getRolePermissions($idrol)
    {
        $query = "SELECT p.idpermiso, p.nombre, p.descripcion 
                  FROM permiso p 
                  INNER JOIN rol_permiso rp ON p.idpermiso = rp.idpermiso 
                  WHERE rp.idrol = $1 
                  ORDER BY p.nombre";
        
        return $this->db->fetchAll($query, [$idrol]);
    }
    
    public function assignPermission($idrol, $idpermiso)
    {
        $query = "INSERT INTO rol_permiso (idrol, idpermiso) VALUES ($1, $2)";
        return $this->db->query($query, [$idrol, $idpermiso]);
    }
    
    public function removePermission($idrol, $idpermiso)
    {
        $query = "DELETE FROM rol_permiso WHERE idrol = $1 AND idpermiso = $2";
        return $this->db->query($query, [$idrol, $idpermiso]);
    }
    
    public function hasPermission($idrol, $idpermiso)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM rol_permiso WHERE idrol = $1 AND idpermiso = $2) as has_permission";
        $result = $this->db->fetchOne($query, [$idrol, $idpermiso]);
        return $result['has_permission'] ?? false;
    }
}