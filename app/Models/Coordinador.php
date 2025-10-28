<?php

namespace App\Models;

use App\Services\DatabaseService;

class Coordinador
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT c.idcoordinador, c.fechacontrato,
                         u.nombre, u.celular, u.username, u.email, u.activo,
                         r.nombre as rol_nombre
                  FROM coordinador c
                  INNER JOIN usuario u ON c.idcoordinador = u.idusuario
                  LEFT JOIN rol r ON u.idrol = r.idrol
                  ORDER BY u.nombre";
        
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT c.idcoordinador, c.fechacontrato,
                         u.nombre, u.celular, u.username, u.email, u.activo,
                         r.nombre as rol_nombre
                  FROM coordinador c
                  INNER JOIN usuario u ON c.idcoordinador = u.idusuario
                  LEFT JOIN rol r ON u.idrol = r.idrol
                  WHERE c.idcoordinador = $1";
        
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function create($userData, $coordinadorData)
    {
        // Primero crear el usuario
        $userModel = new \App\Models\User();
        $user = $userModel->create($userData);
        
        if (!$user) {
            return null;
        }
        
        // Luego crear el coordinador
        $query = "INSERT INTO coordinador (idcoordinador, fechacontrato) 
                  VALUES ($1, $2) 
                  RETURNING idcoordinador, fechacontrato";
        
        return $this->db->fetchOne($query, [
            $user['idusuario'],
            $coordinadorData['fechacontrato'] ?? null
        ]);
    }
    
    public function update($id, $userData = null, $coordinadorData = null)
    {
        // Actualizar usuario si se proporcionan datos
        if ($userData) {
            $userModel = new \App\Models\User();
            $userModel->update($id, $userData);
        }
        
        // Actualizar datos específicos del coordinador
        if ($coordinadorData) {
            $query = "UPDATE coordinador 
                      SET fechacontrato = $1 
                      WHERE idcoordinador = $2 
                      RETURNING idcoordinador, fechacontrato";
            
            return $this->db->fetchOne($query, [
                $coordinadorData['fechacontrato'] ?? null,
                $id
            ]);
        }
        
        return $this->findById($id);
    }
    
    public function delete($id)
    {
        // Al eliminar el usuario, se elimina automáticamente el coordinador por CASCADE
        $userModel = new \App\Models\User();
        return $userModel->delete($id);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM coordinador WHERE idcoordinador = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return $result['exists'] ?? false;
    }
}