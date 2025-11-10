<?php

namespace App\Services;

class DatabaseService
{
    /** @var resource|null */
    private $conn = null;

    private function connect(): void
    {
        $cfg = config('database.connections.pgsql');

        $host     = $cfg['host']     ?? '127.0.0.1';
        $port     = $cfg['port']     ?? '5432';
        $dbname   = $cfg['database'] ?? 'postgres';
        $user     = $cfg['username'] ?? 'postgres';
        $password = $cfg['password'] ?? '';
        $sslmode  = $cfg['sslmode']  ?? 'disable';

        $connStr = sprintf(
            "host=%s port=%s dbname=%s user=%s password=%s sslmode=%s",
            $host, $port, $dbname, $user, $password, $sslmode
        );

        $this->conn = @pg_connect($connStr);
        if (!$this->conn) {
            $pgErr = @pg_last_error() ?: 'sin detalle';
            throw new \RuntimeException('No se pudo conectar a PostgreSQL. Detalle: '.$pgErr);
        }
    }

    /** Asegura conexión viva; si está cerrada o BAD, reconecta. */
    private function ensureConnection(): void
    {
        if (!$this->conn) {
            $this->connect();
            return;
        }

        $status = @pg_connection_status($this->conn);
        if ($status !== PGSQL_CONNECTION_OK) {
            // intenta reconectar
            @pg_close($this->conn);
            $this->conn = null;
            $this->connect();
            return;
        }

        // ping defensivo (algunos drivers marcan OK pero no responden)
        if (@pg_ping($this->conn) === false) {
            @pg_close($this->conn);
            $this->conn = null;
            $this->connect();
        }
    }

    public function query(string $sql, array $params = []): array
    {
        $this->ensureConnection();

        $result = $params
            ? @pg_query_params($this->conn, $sql, $params)
            : @pg_query($this->conn, $sql);

        if ($result === false) {
            $err = @pg_last_error($this->conn) ?: 'Error desconocido en la consulta.';
            throw new \RuntimeException($err);
        }

        $rows = @pg_fetch_all($result);
        return $rows ?: [];
    }

    public function exec(string $sql, array $params = []): int
    {
        $this->ensureConnection();

        $result = $params
            ? @pg_query_params($this->conn, $sql, $params)
            : @pg_query($this->conn, $sql);

        if ($result === false) {
            $err = @pg_last_error($this->conn) ?: 'Error desconocido en exec.';
            throw new \RuntimeException($err);
        }

        return @pg_affected_rows($result) ?: 0;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params);
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $rows = $this->query($sql, $params);
        return $rows[0] ?? null;
    }

    public function __destruct()
    {
        try {
            if ($this->conn && \function_exists('pg_connection_status')) {
                if (@pg_connection_status($this->conn) === PGSQL_CONNECTION_OK) {
                    @pg_close($this->conn);
                }
            }
        } catch (\Throwable $e) {
            // silencioso
        } finally {
            $this->conn = null;
        }
    }
}
