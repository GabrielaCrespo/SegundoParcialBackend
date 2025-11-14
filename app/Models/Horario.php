<?php

namespace App\Models;

use App\Services\DatabaseService;

class Horario
{
    private $db;
    
    // Días válidos
    const DIAS_VALIDOS = ['LU', 'MA', 'MI', 'JU', 'VI', 'SA'];
    
    public function __construct()
    {
        $this->db = new DatabaseService();
    }
    
    public function findAll()
    {
        $query = "SELECT idhorario, dia, horainicio, horafinal 
                  FROM horario 
                  ORDER BY 
                    CASE dia 
                      WHEN 'LU' THEN 1 
                      WHEN 'MA' THEN 2 
                      WHEN 'MI' THEN 3 
                      WHEN 'JU' THEN 4 
                      WHEN 'VI' THEN 5 
                      WHEN 'SA' THEN 6 
                    END, 
                    horainicio";
        return $this->db->fetchAll($query);
    }
    
    public function findById($id)
    {
        $query = "SELECT idhorario, dia, horainicio, horafinal 
                  FROM horario 
                  WHERE idhorario = $1";
        return $this->db->fetchOne($query, [$id]);
    }
    
    public function findByDia($dia)
    {
        $query = "SELECT idhorario, dia, horainicio, horafinal 
                  FROM horario 
                  WHERE dia = $1 
                  ORDER BY horainicio";
        return $this->db->fetchAll($query, [$dia]);
    }
    
    public function create($data)
    {
        // Validar día
        if (!$this->validateDia($data['dia'])) {
            throw new \InvalidArgumentException('Día no válido. Debe ser: ' . implode(', ', self::DIAS_VALIDOS));
        }
        
        // Validar horarios
        if (!$this->validateHorarios($data['horainicio'], $data['horafinal'])) {
            throw new \InvalidArgumentException('La hora final debe ser mayor que la hora inicial');
        }
        
        $query = "INSERT INTO horario (dia, horainicio, horafinal) 
                  VALUES ($1, $2, $3) 
                  RETURNING idhorario, dia, horainicio, horafinal";
        
        return $this->db->fetchOne($query, [
            $data['dia'],
            $data['horainicio'],
            $data['horafinal']
        ]);
    }
    
    public function update($id, $data)
    {
        // Validar día
        if (!$this->validateDia($data['dia'])) {
            throw new \InvalidArgumentException('Día no válido. Debe ser: ' . implode(', ', self::DIAS_VALIDOS));
        }
        
        // Validar horarios
        if (!$this->validateHorarios($data['horainicio'], $data['horafinal'])) {
            throw new \InvalidArgumentException('La hora final debe ser mayor que la hora inicial');
        }
        
        $query = "UPDATE horario 
                  SET dia = $1, horainicio = $2, horafinal = $3 
                  WHERE idhorario = $4 
                  RETURNING idhorario, dia, horainicio, horafinal";
        
        return $this->db->fetchOne($query, [
            $data['dia'],
            $data['horainicio'],
            $data['horafinal'],
            $id
        ]);
    }
    
    public function delete($id)
    {
        $query = "DELETE FROM horario WHERE idhorario = $1";
        return $this->db->query($query, [$id]);
    }
    
    public function exists($id)
    {
        $query = "SELECT EXISTS(SELECT 1 FROM horario WHERE idhorario = $1) as exists";
        $result = $this->db->fetchOne($query, [$id]);
        return ($result['exists'] ?? 'f') === 't';
    }
    
    public function findByRangoHoras($horaInicio, $horaFin)
    {
        $query = "SELECT idhorario, dia, horainicio, horafinal 
                  FROM horario 
                  WHERE horainicio >= $1 AND horafinal <= $2 
                  ORDER BY dia, horainicio";
        return $this->db->fetchAll($query, [$horaInicio, $horaFin]);
    }
    
    public function checkConflicto($dia, $horainicio, $horafinal, $excludeId = null)
    {
        $query = "SELECT idhorario, dia, horainicio, horafinal 
                  FROM horario 
                  WHERE dia = $1 
                    AND (
                      (horainicio <= $2 AND horafinal > $2) OR 
                      (horainicio < $3 AND horafinal >= $3) OR 
                      (horainicio >= $2 AND horafinal <= $3)
                    )";
        
        $params = [$dia, $horainicio, $horafinal];
        
        if ($excludeId) {
            $query .= " AND idhorario != $4";
            $params[] = $excludeId;
        }
        
        return $this->db->fetchAll($query, $params);
    }
    
    public function validateDia($dia)
    {
        return in_array(strtoupper($dia), self::DIAS_VALIDOS);
    }
    
    public function validateHorarios($horainicio, $horafinal)
    {
        return strtotime($horafinal) > strtotime($horainicio);
    }
    
    public static function getDiasValidos()
    {
        return self::DIAS_VALIDOS;
    }
    
    public function getDiaNombre($dia)
    {
        $nombres = [
            'LU' => 'Lunes',
            'MA' => 'Martes', 
            'MI' => 'Miércoles',
            'JU' => 'Jueves',
            'VI' => 'Viernes',
            'SA' => 'Sábado'
        ];
        
        return $nombres[$dia] ?? $dia;
    }
}