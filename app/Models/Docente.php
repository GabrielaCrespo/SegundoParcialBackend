<?php

namespace App\Models;

use App\Services\DatabaseService;

class Docente
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT d.iddocente, d.especialidad, d.fechacontrato,
                         u.nombre, u.celular, u.username, u.email, u.activo,
                         r.nombre as rol_nombre
                  FROM docente d
                  INNER JOIN usuario u ON d.iddocente = u.idusuario
                  LEFT JOIN rol r ON u.idrol = r.idrol
                  ORDER BY u.nombre";
        
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT d.iddocente, d.especialidad, d.fechacontrato,
                         u.nombre, u.celular, u.username, u.email, u.activo,
                         r.nombre as rol_nombre
                  FROM docente d
                  INNER JOIN usuario u ON d.iddocente = u.idusuario
                  LEFT JOIN rol r ON u.idrol = r.idrol
                  WHERE d.iddocente = $1";
        
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function create($userData, $docenteData)
    {
        // Primero crear el usuario
        $userModel = new \App\Models\User();
        $user = $userModel->create($userData);
        
        if (!$user) {
            return null;
        }
        
        // Luego crear el docente
        $query = "INSERT INTO docente (iddocente, especialidad, fechacontrato) 
                  VALUES ($1, $2, $3) 
                  RETURNING iddocente, especialidad, fechacontrato";
        
        return $this->db->fetchOne($query, [
            $user['idusuario'],
            $docenteData['especialidad'] ?? null,
            $docenteData['fechacontrato'] ?? null
        ]);
    }
    
    public function update($id, $userData = null, $docenteData = null)
    {
        // Actualizar usuario si se proporcionan datos
        if ($userData) {
            $userModel = new \App\Models\User();
            $userModel->update($id, $userData);
        }
        
        // Actualizar datos específicos del docente
        if ($docenteData) {
            $query = "UPDATE docente 
                      SET especialidad = $1, fechacontrato = $2 
                      WHERE iddocente = $3 
                      RETURNING iddocente, especialidad, fechacontrato";
            
            return $this->db->fetchOne($query, [
                $docenteData['especialidad'] ?? null,
                $docenteData['fechacontrato'] ?? null,
                $id
            ]);
        }
        
        return $this->findById($id);
    }
    
    public function delete($id)
    {
        // Al eliminar el usuario, se elimina automáticamente el docente por CASCADE
        $userModel = new \App\Models\User();
        return $userModel->delete($id);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM docente WHERE iddocente = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return $result['exists'] ?? false;
    }
    
    public function findByEspecialidad($especialidad)
    {
        $query = "SELECT d.iddocente, d.especialidad, d.fechacontrato,
                         u.nombre, u.celular, u.username, u.email, u.activo,
                         r.nombre as rol_nombre
                  FROM docente d
                  INNER JOIN usuario u ON d.iddocente = u.idusuario
                  LEFT JOIN rol r ON u.idrol = r.idrol
                  WHERE d.especialidad ILIKE $1
                  ORDER BY u.nombre";
        
        return $this->db->fetchAll($query, ['%' . $especialidad . '%']);
    }
}