<?php

namespace App\Models;

use App\Services\DatabaseService;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseService();
    }

    public function findByEmail($email)
    {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuario u 
                  LEFT JOIN rol r ON u.idrol = r.idrol 
                  WHERE u.email = $1";
        
        return $this->db->fetchOne($query, [$email]);
    }

    public function findByUsername($username)
    {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuario u 
                  LEFT JOIN rol r ON u.idrol = r.idrol 
                  WHERE u.username = $1";
        
        return $this->db->fetchOne($query, [$username]);
    }

    public function findById($id)
    {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuario u 
                  LEFT JOIN rol r ON u.idrol = r.idrol 
                  WHERE u.idusuario = $1";
        
        return $this->db->fetchOne($query, [$id]);
    }

    public function create($data)
    {
        $query = "INSERT INTO usuario (nombre, celular, username, email, password, activo, idrol) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7) 
                  RETURNING idusuario, nombre, celular, username, email, activo, idrol";
        
        return $this->db->fetchOne($query, [
            $data['nombre'],
            $data['celular'] ?? null,
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['activo'] ?? true,
            $data['idrol']
        ]);
    }

    public function update($id, $data)
    {
        $query = "UPDATE usuario 
                  SET nombre = $1, celular = $2, username = $3, email = $4, activo = $5";
        
        $params = [
            $data['nombre'],
            $data['celular'] ?? null,
            $data['username'],
            $data['email'],
            $data['activo'] ?? true
        ];
        
        $paramCount = 6;

        if (isset($data['password'])) {
            $query .= ", password = $" . $paramCount;
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $paramCount++;
        }

        if (isset($data['idrol'])) {
            $query .= ", idrol = $" . $paramCount;
            $params[] = $data['idrol'];
            $paramCount++;
        }

        $query .= " WHERE idusuario = $" . $paramCount . " 
                   RETURNING idusuario, nombre, celular, username, email, activo, idrol";
        $params[] = $id;

        return $this->db->fetchOne($query, $params);
    }

    public function delete($id)
    {
        $query = "DELETE FROM usuario WHERE idusuario = $1";
        return $this->db->query($query, [$id]);
    }

    public function getAll()
    {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM usuario u 
                  LEFT JOIN rol r ON u.idrol = r.idrol 
                  ORDER BY u.nombre";
        
        return $this->db->fetchAll($query);
    }

    public function verifyPassword($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }
}
