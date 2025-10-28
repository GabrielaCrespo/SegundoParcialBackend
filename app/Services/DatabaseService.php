<?php

namespace App\Services;

use Exception;

class DatabaseService
{
    private $connection;
    
    public function __construct()
    {
        $this->connect();
    }
    
    private function connect()
    {
        $host = env('DB_HOST');
        $port = env('DB_PORT');
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        
        $connString = "host=$host port=$port dbname=$database user=$username password=$password sslmode=require";
        
        $this->connection = pg_connect($connString);
        
        if (!$this->connection) {
            throw new Exception("Error al conectar a PostgreSQL");
        }
    }
    
    public function query($sql, $params = [])
    {
        if (empty($params)) {
            $result = pg_query($this->connection, $sql);
        } else {
            $result = pg_query_params($this->connection, $sql, $params);
        }
        
        if (!$result) {
            throw new Exception("Error en consulta: " . pg_last_error($this->connection));
        }
        
        return $result;
    }
    
    public function fetchAll($query, $params = [])
    {
        $result = $this->query($query, $params);
        $rows = [];
        while ($row = pg_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function fetchOne($query, $params = [])
    {
        $result = $this->query($query, $params);
        return pg_fetch_assoc($result);
    }
    
    public function __destruct()
    {
        if ($this->connection && is_resource($this->connection)) {
            @pg_close($this->connection);
        }
    }
}