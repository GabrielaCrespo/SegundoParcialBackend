<?php

namespace App\Models;

use App\Services\DatabaseService;

class User
{
    private $db;

    private const SELECT_FIELDS = "
        u.idusuario,
        u.nombre,
        u.celular,
        u.username,
        u.email,
        u.password, 
        u.activo,
        u.idrol,
        r.nombre AS rol_nombre
    ";

    public function __construct()
    {
        $this->db = new DatabaseService();
    }

    public function findByEmail($email): ?array
    {
        $sql = "SELECT ".self::SELECT_FIELDS."
                FROM usuario u
                LEFT JOIN rol r ON u.idrol = r.idrol
                WHERE u.email = $1
                LIMIT 1";
        return $this->db->fetchOne($sql, [$email]) ?: null;
    }

    public function findByUsername($username): ?array
    {
        $sql = "SELECT ".self::SELECT_FIELDS."
                FROM usuario u
                LEFT JOIN rol r ON u.idrol = r.idrol
                WHERE u.username = $1
                LIMIT 1";
        return $this->db->fetchOne($sql, [$username]) ?: null;
    }

    public function findById($id): ?array
    {
        $sql = "SELECT ".self::SELECT_FIELDS."
                FROM usuario u
                LEFT JOIN rol r ON u.idrol = r.idrol
                WHERE u.idusuario = $1
                LIMIT 1";
        return $this->db->fetchOne($sql, [$id]) ?: null;
    }

    public function getAll(): array
    {
        $sql = "SELECT ".self::SELECT_FIELDS."
                FROM usuario u
                LEFT JOIN rol r ON u.idrol = r.idrol
                ORDER BY u.idusuario";
        return $this->db->fetchAll($sql);
    }

    public function create($data): ?array
    {
        $passwordHash = !empty($data['password'])
            ? password_hash($data['password'], PASSWORD_DEFAULT)
            : null;

        $sql = "INSERT INTO usuario (nombre, celular, username, email, password, activo, idrol)
                VALUES ($1, $2, $3, $4, $5, $6, $7)
                RETURNING idusuario";

        $row = $this->db->fetchOne($sql, [
            $data['nombre'] ?? null,
            $data['celular'] ?? null,
            $data['username'],
            $data['email'],
            $passwordHash,
            $data['activo'] ?? true,
            $data['idrol'],
        ]);

        $id = $row['idusuario'] ?? null;
        return $id ? $this->findById($id) : null;
    }

    public function update($id, $data): ?array
    {
        $sets = [];
        $params = [];
        $i = 1;

        foreach (['nombre','celular','username','email','activo','idrol','password'] as $field) {
            if (!array_key_exists($field, $data)) continue;

            if ($field === 'password') {
                if ($data['password'] === '' || $data['password'] === null) continue;
                $value = password_hash($data['password'], PASSWORD_DEFAULT);
                $sets[] = "password = $" . $i;
                $params[] = $value;
                $i++;
                continue;
            }

            $sets[] = "$field = $" . $i;
            $params[] = $data[$field] ?? null;
            $i++;
        }

        if ($sets) {
            $params[] = $id;
            $sql = "UPDATE usuario SET ".implode(', ', $sets)." WHERE idusuario = $".$i;
            $this->db->exec($sql, $params);
        }

        return $this->findById($id);
    }

    public function delete($id): bool
    {
        $n = $this->db->exec("DELETE FROM usuario WHERE idusuario = $1", [$id]);
        return $n > 0;
    }

    public function verifyPassword($password, $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

    public function toggleStatus(int $id): ?array
    {
        $sql = 'UPDATE usuario SET activo = NOT activo WHERE idusuario = $1 RETURNING idusuario, activo';
        return $this->db->fetchOne($sql, [$id]);
    }



}
