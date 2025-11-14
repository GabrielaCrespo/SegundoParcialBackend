<?php

namespace App\Models;

use App\Services\DatabaseService;

class Grupo
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseService();
    }

    /**
     * Obtener todos los grupos con información de materia y gestión
     */
    public function findAll()
    {
        $query = "SELECT g.idgrupo, g.nombre_grupo, g.capacidad,
                         g.idmateria, m.nombre as materia_nombre, m.sigla as materia_sigla,
                         g.idgestion, ge.anio, ge.periodo,
                         CONCAT(ge.anio, '/', ge.periodo) as gestion_nombre
                  FROM grupo g
                  INNER JOIN materia m ON g.idmateria = m.idmateria
                  INNER JOIN gestion ge ON g.idgestion = ge.idgestion
                  ORDER BY ge.anio DESC, ge.periodo, m.nombre, g.nombre_grupo";
        
        return $this->db->fetchAll($query);
    }

    /**
     * Obtener un grupo por ID
     */
    public function findById($idgrupo)
    {
        $query = "SELECT g.idgrupo, g.nombre_grupo, g.capacidad,
                         g.idmateria, m.nombre as materia_nombre, m.sigla as materia_sigla,
                         g.idgestion, ge.anio, ge.periodo,
                         CONCAT(ge.anio, '/', ge.periodo) as gestion_nombre
                  FROM grupo g
                  INNER JOIN materia m ON g.idmateria = m.idmateria
                  INNER JOIN gestion ge ON g.idgestion = ge.idgestion
                  WHERE g.idgrupo = $1";
        
        return $this->db->fetchOne($query, [$idgrupo]);
    }

    /**
     * Obtener grupos por gestión
     */
    public function findByGestion($idgestion)
    {
        $query = "SELECT g.idgrupo, g.nombre_grupo, g.capacidad,
                         g.idmateria, m.nombre as materia_nombre, m.sigla as materia_sigla,
                         g.idgestion, ge.anio, ge.periodo,
                         CONCAT(ge.anio, '/', ge.periodo) as gestion_nombre
                  FROM grupo g
                  INNER JOIN materia m ON g.idmateria = m.idmateria
                  INNER JOIN gestion ge ON g.idgestion = ge.idgestion
                  WHERE g.idgestion = $1
                  ORDER BY m.nombre, g.nombre_grupo";
        
        return $this->db->fetchAll($query, [$idgestion]);
    }

    /**
     * Obtener grupos por materia y gestión
     */
    public function findByMateriaYGestion($idmateria, $idgestion)
    {
        $query = "SELECT g.idgrupo, g.nombre_grupo, g.capacidad,
                         g.idmateria, m.nombre as materia_nombre, m.sigla as materia_sigla,
                         g.idgestion
                  FROM grupo g
                  INNER JOIN materia m ON g.idmateria = m.idmateria
                  WHERE g.idmateria = $1 AND g.idgestion = $2
                  ORDER BY g.nombre_grupo";
        
        return $this->db->fetchAll($query, [$idmateria, $idgestion]);
    }

    /**
     * Crear un nuevo grupo
     */
    public function create($data)
    {
        $query = "INSERT INTO grupo (nombre_grupo, idmateria, idgestion, capacidad)
                  VALUES ($1, $2, $3, $4)
                  RETURNING idgrupo, nombre_grupo, idmateria, idgestion, capacidad";

        return $this->db->fetchOne($query, [
            $data['nombre_grupo'],
            $data['idmateria'],
            $data['idgestion'],
            $data['capacidad'] ?? 30
        ]);
    }

    /**
     * Actualizar un grupo
     */
    public function update($idgrupo, $data)
    {
        $query = "UPDATE grupo
                  SET nombre_grupo = $1, capacidad = $2
                  WHERE idgrupo = $3
                  RETURNING idgrupo, nombre_grupo, idmateria, idgestion, capacidad";

        return $this->db->fetchOne($query, [
            $data['nombre_grupo'],
            $data['capacidad'] ?? 30,
            $idgrupo
        ]);
    }

    /**
     * Eliminar un grupo
     */
    public function delete($idgrupo)
    {
        $query = "DELETE FROM grupo WHERE idgrupo = $1";
        return $this->db->query($query, [$idgrupo]);
    }

    /**
     * Verificar si existe un grupo con el mismo nombre para la misma materia y gestión
     */
    public function existsByNombreMateriaGestion($nombreGrupo, $idmateria, $idgestion, $excludeId = null)
    {
        if ($excludeId) {
            $query = "SELECT EXISTS(SELECT 1 FROM grupo 
                      WHERE nombre_grupo = $1 AND idmateria = $2 AND idgestion = $3 AND idgrupo != $4) as exists";
            $result = $this->db->fetchOne($query, [$nombreGrupo, $idmateria, $idgestion, $excludeId]);
        } else {
            $query = "SELECT EXISTS(SELECT 1 FROM grupo 
                      WHERE nombre_grupo = $1 AND idmateria = $2 AND idgestion = $3) as exists";
            $result = $this->db->fetchOne($query, [$nombreGrupo, $idmateria, $idgestion]);
        }
        
        // PostgreSQL retorna 't' o 'f' como string, convertir a booleano
        return ($result['exists'] ?? 'f') === 't';
    }

    /**
     * Verificar si un grupo existe
     */
    public function exists($idgrupo)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM grupo WHERE idgrupo = $1) as exists";
        $result = $this->db->fetchOne($query, [$idgrupo]);
        // PostgreSQL retorna 't' o 'f' como string, convertir a booleano
        return ($result['exists'] ?? 'f') === 't';
    }
}