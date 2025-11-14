<?php

namespace App\Models;

use App\Services\DatabaseService;

class Asignacion
{
    private $db;
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    /**
     * Obtener todas las asignaciones con información completa
     */
    public function findAll($filters = [])
    {
        $whereConditions = [];
        $params = [];
        $paramCount = 1;

        // Construir filtros dinámicos
        if (!empty($filters['idgestion'])) {
            $whereConditions[] = "a.idgestion = $" . $paramCount++;
            $params[] = $filters['idgestion'];
        }
        if (!empty($filters['iddocente'])) {
            $whereConditions[] = "a.iddocente = $" . $paramCount++;
            $params[] = $filters['iddocente'];
        }
        if (!empty($filters['idaula'])) {
            $whereConditions[] = "a.idaula = $" . $paramCount++;
            $params[] = $filters['idaula'];
        }
        if (!empty($filters['idmateria'])) {
            $whereConditions[] = "a.idmateria = $" . $paramCount++;
            $params[] = $filters['idmateria'];
        }
        if (!empty($filters['dia'])) {
            $whereConditions[] = "h.dia = $" . $paramCount++;
            $params[] = $filters['dia'];
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $query = "SELECT DISTINCT ON (a.idasignacion)
                         a.idasignacion, a.idgrupo, a.idmateria, a.iddocente, a.idaula, a.idgestion,
                         g.nombre_grupo,
                         m.nombre as materia_nombre, m.sigla as materia_sigla,
                         u.nombre as docente_nombre,
                         au.numero as aula_numero,
                         ge.anio, ge.periodo,
                         array_agg(DISTINCT h.idhorario) as horarios_ids,
                         array_agg(DISTINCT h.dia || ' ' || h.horainicio || '-' || h.horafinal) as horarios_texto
                  FROM asignacion a
                  INNER JOIN grupo g ON a.idgrupo = g.idgrupo
                  INNER JOIN materia m ON a.idmateria = m.idmateria
                  INNER JOIN docente d ON a.iddocente = d.iddocente
                  INNER JOIN usuario u ON d.iddocente = u.idusuario
                  INNER JOIN aula au ON a.idaula = au.idaula
                  INNER JOIN gestion ge ON a.idgestion = ge.idgestion
                  LEFT JOIN asignacion_horario ah ON a.idasignacion = ah.idasignacion
                  LEFT JOIN horario h ON ah.idhorario = h.idhorario
                  $whereClause
                  GROUP BY a.idasignacion, a.idgrupo, a.idmateria, a.iddocente, a.idaula, a.idgestion,
                           g.nombre_grupo, m.nombre, m.sigla, u.nombre, au.numero, ge.anio, ge.periodo
                  ORDER BY a.idasignacion, ge.anio DESC, ge.periodo, m.nombre, g.nombre_grupo";
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Obtener asignaciones con horarios detallados
     */
    public function findAllWithHorarios($filters = [])
    {
        $whereConditions = [];
        $params = [];
        $paramCount = 1;

        if (!empty($filters['idgestion'])) {
            $whereConditions[] = "a.idgestion = $" . $paramCount++;
            $params[] = $filters['idgestion'];
        }
        if (!empty($filters['iddocente'])) {
            $whereConditions[] = "a.iddocente = $" . $paramCount++;
            $params[] = $filters['iddocente'];
        }
        if (!empty($filters['idaula'])) {
            $whereConditions[] = "a.idaula = $" . $paramCount++;
            $params[] = $filters['idaula'];
        }
        if (!empty($filters['idmateria'])) {
            $whereConditions[] = "a.idmateria = $" . $paramCount++;
            $params[] = $filters['idmateria'];
        }
        if (!empty($filters['dia'])) {
            $whereConditions[] = "h.dia = $" . $paramCount++;
            $params[] = $filters['dia'];
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $query = "SELECT a.idasignacion, a.idgrupo, a.idmateria, a.iddocente, a.idaula, a.idgestion,
                         g.nombre_grupo,
                         m.nombre as materia_nombre, m.sigla as materia_sigla,
                         u.nombre as docente_nombre,
                         au.numero as aula_numero,
                         ge.anio, ge.periodo,
                         h.idhorario, h.dia, h.horainicio, h.horafinal
                  FROM asignacion a
                  INNER JOIN grupo g ON a.idgrupo = g.idgrupo
                  INNER JOIN materia m ON a.idmateria = m.idmateria
                  INNER JOIN docente d ON a.iddocente = d.iddocente
                  INNER JOIN usuario u ON d.iddocente = u.idusuario
                  INNER JOIN aula au ON a.idaula = au.idaula
                  INNER JOIN gestion ge ON a.idgestion = ge.idgestion
                  LEFT JOIN asignacion_horario ah ON a.idasignacion = ah.idasignacion
                  LEFT JOIN horario h ON ah.idhorario = h.idhorario
                  $whereClause
                  ORDER BY ge.anio DESC, ge.periodo, m.nombre, g.nombre_grupo, 
                           CASE h.dia 
                             WHEN 'LU' THEN 1 
                             WHEN 'MA' THEN 2 
                             WHEN 'MI' THEN 3 
                             WHEN 'JU' THEN 4 
                             WHEN 'VI' THEN 5 
                             WHEN 'SA' THEN 6 
                           END, 
                           h.horainicio";
        
        $results = $this->db->fetchAll($query, $params);
        
        // Agrupar horarios por asignación
        $grouped = [];
        foreach ($results as $row) {
            $id = $row['idasignacion'];
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'idasignacion' => $row['idasignacion'],
                    'idgrupo' => $row['idgrupo'],
                    'nombre_grupo' => $row['nombre_grupo'],
                    'idmateria' => $row['idmateria'],
                    'materia_nombre' => $row['materia_nombre'],
                    'materia_sigla' => $row['materia_sigla'],
                    'iddocente' => $row['iddocente'],
                    'docente_nombre' => $row['docente_nombre'],
                    'idaula' => $row['idaula'],
                    'aula_numero' => $row['aula_numero'],
                    'idgestion' => $row['idgestion'],
                    'anio' => $row['anio'],
                    'periodo' => $row['periodo'],
                    'horarios' => []
                ];
            }
            
            if ($row['idhorario']) {
                $grouped[$id]['horarios'][] = [
                    'idhorario' => $row['idhorario'],
                    'dia' => $row['dia'],
                    'horainicio' => $row['horainicio'],
                    'horafinal' => $row['horafinal']
                ];
            }
        }
        
        return array_values($grouped);
    }
    
    /**
     * Obtener una asignación por ID
     */
    public function findById($id)
    {
        $query = "SELECT a.idasignacion, a.idgrupo, a.idmateria, a.iddocente, a.idaula, a.idgestion,
                         g.nombre_grupo,
                         m.nombre as materia_nombre, m.sigla as materia_sigla,
                         u.nombre as docente_nombre,
                         au.numero as aula_numero,
                         ge.anio, ge.periodo
                  FROM asignacion a
                  INNER JOIN grupo g ON a.idgrupo = g.idgrupo
                  INNER JOIN materia m ON a.idmateria = m.idmateria
                  INNER JOIN docente d ON a.iddocente = d.iddocente
                  INNER JOIN usuario u ON d.iddocente = u.idusuario
                  INNER JOIN aula au ON a.idaula = au.idaula
                  INNER JOIN gestion ge ON a.idgestion = ge.idgestion
                  WHERE a.idasignacion = $1";
        
        return $this->db->fetchOne($query, [$id]);
    }
    
    /**
     * Crear una nueva asignación
     */
    public function create($data)
    {
        $query = "INSERT INTO asignacion (idgrupo, idmateria, iddocente, idaula, idgestion) 
                  VALUES ($1, $2, $3, $4, $5) 
                  RETURNING idasignacion, idgrupo, idmateria, iddocente, idaula, idgestion";
        
        return $this->db->fetchOne($query, [
            $data['idgrupo'],
            $data['idmateria'],
            $data['iddocente'],
            $data['idaula'],
            $data['idgestion']
        ]);
    }
    
    /**
     * Actualizar una asignación
     */
    public function update($id, $data)
    {
        $query = "UPDATE asignacion 
                  SET idgrupo = $1, idmateria = $2, iddocente = $3, idaula = $4, idgestion = $5 
                  WHERE idasignacion = $6 
                  RETURNING idasignacion, idgrupo, idmateria, iddocente, idaula, idgestion";
        
        return $this->db->fetchOne($query, [
            $data['idgrupo'],
            $data['idmateria'],
            $data['iddocente'],
            $data['idaula'],
            $data['idgestion'],
            $id
        ]);
    }
    
    /**
     * Eliminar una asignación
     */
    public function delete($id)
    {
        $query = "DELETE FROM asignacion WHERE idasignacion = $1";
        return $this->db->query($query, [$id]);
    }
    
    /**
     * Verificar si existe una asignación
     */
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM asignacion WHERE idasignacion = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return ($result['exists'] ?? 'f') === 't';
    }
    
    /**
     * Verificar conflictos de horario para docente, aula o grupo
     */
    public function checkConflicts($idgestion, $iddocente, $idaula, $idgrupo, $horarios, $excludeAsignacionId = null)
    {
        $conflicts = [];
        
        foreach ($horarios as $idhorario) {
            // 1. Conflicto de docente (mismo docente, mismo horario)
            $excludeClause = $excludeAsignacionId ? "AND a.idasignacion != $4" : "";
            $queryDocente = "
                SELECT a.idasignacion, m.nombre as materia, g.nombre_grupo as grupo
                FROM asignacion a
                JOIN asignacion_horario ah ON a.idasignacion = ah.idasignacion
                JOIN materia m ON a.idmateria = m.idmateria
                JOIN grupo g ON a.idgrupo = g.idgrupo
                WHERE a.idgestion = $1 
                  AND a.iddocente = $2 
                  AND ah.idhorario = $3
                  $excludeClause
                LIMIT 1
            ";
            
            $params = $excludeAsignacionId 
                ? [$idgestion, $iddocente, $idhorario, $excludeAsignacionId]
                : [$idgestion, $iddocente, $idhorario];
            
            $docenteConflict = $this->db->fetchOne($queryDocente, $params);
            
            if ($docenteConflict) {
                $conflicts[] = [
                    'tipo' => 'docente',
                    'idhorario' => $idhorario,
                    'mensaje' => "El docente ya tiene asignada la materia '{$docenteConflict['materia']}' grupo '{$docenteConflict['grupo']}' en este horario"
                ];
            }
            
            // 2. Conflicto de aula (misma aula, mismo horario)
            $queryAula = "
                SELECT a.idasignacion, m.nombre as materia, g.nombre_grupo as grupo, u.nombre as docente
                FROM asignacion a
                JOIN asignacion_horario ah ON a.idasignacion = ah.idasignacion
                JOIN materia m ON a.idmateria = m.idmateria
                JOIN grupo g ON a.idgrupo = g.idgrupo
                JOIN docente d ON a.iddocente = d.iddocente
                JOIN usuario u ON d.iddocente = u.idusuario
                WHERE a.idgestion = $1 
                  AND a.idaula = $2 
                  AND ah.idhorario = $3
                  $excludeClause
                LIMIT 1
            ";
            
            $params = $excludeAsignacionId 
                ? [$idgestion, $idaula, $idhorario, $excludeAsignacionId]
                : [$idgestion, $idaula, $idhorario];
            
            $aulaConflict = $this->db->fetchOne($queryAula, $params);
            
            if ($aulaConflict) {
                $conflicts[] = [
                    'tipo' => 'aula',
                    'idhorario' => $idhorario,
                    'mensaje' => "El aula ya está ocupada por '{$aulaConflict['materia']}' grupo '{$aulaConflict['grupo']}' (docente: {$aulaConflict['docente']}) en este horario"
                ];
            }
            
            // 3. Conflicto de grupo (mismo grupo, mismo horario)
            $queryGrupo = "
                SELECT a.idasignacion, m.nombre as materia
                FROM asignacion a
                JOIN asignacion_horario ah ON a.idasignacion = ah.idasignacion
                JOIN materia m ON a.idmateria = m.idmateria
                WHERE a.idgestion = $1 
                  AND a.idgrupo = $2 
                  AND ah.idhorario = $3
                  $excludeClause
                LIMIT 1
            ";
            
            $params = $excludeAsignacionId 
                ? [$idgestion, $idgrupo, $idhorario, $excludeAsignacionId]
                : [$idgestion, $idgrupo, $idhorario];
            
            $grupoConflict = $this->db->fetchOne($queryGrupo, $params);
            
            if ($grupoConflict) {
                $conflicts[] = [
                    'tipo' => 'grupo',
                    'idhorario' => $idhorario,
                    'mensaje' => "El grupo ya tiene asignada la materia '{$grupoConflict['materia']}' en este horario"
                ];
            }
        }
        
        return $conflicts;
    }
    
    /**
     * Eliminar asignaciones por gestión
     */
    public function deleteByGestion($idgestion)
    {
        $query = "DELETE FROM asignacion WHERE idgestion = $1";
        return $this->db->query($query, [$idgestion]);
    }
}