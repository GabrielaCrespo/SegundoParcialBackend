<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Tabla de bitácora (solo inserciones; no se actualiza ni elimina)
        DB::statement("
            CREATE TABLE IF NOT EXISTS bitacora (
                idbitacora      BIGSERIAL PRIMARY KEY,
                tabla           TEXT NOT NULL,
                operacion       TEXT NOT NULL, -- 'INSERT' | 'UPDATE' | 'DELETE'
                idregistro      TEXT NULL,     -- PK simple; en pivotes guardamos JSON en pk_json
                pk_json         JSONB NULL,    -- para PK compuestas (ej. 'rol_permiso', 'materia_carrera')
                datos_old       JSONB NULL,
                datos_new       JSONB NULL,
                db_usuario      TEXT NULL,     -- rol/usuario de BD que ejecutó la query
                ip_cliente      INET NULL,     -- IP del cliente BD
                creado_en       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
            );
        ");

        // 2) Índices útiles
        DB::statement("CREATE INDEX IF NOT EXISTS idx_bitacora_tabla ON bitacora (tabla);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_bitacora_operacion ON bitacora (operacion);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_bitacora_creado_en ON bitacora (creado_en);");

        // 3) Función genérica para registrar cambios (PL/pgSQL)
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_log_bitacora() RETURNS trigger AS $$
            DECLARE
                v_tabla TEXT := TG_TABLE_NAME;
                v_op    TEXT := TG_OP;
                v_id    TEXT := NULL;
                v_pk    JSONB := NULL;
            BEGIN
                -- Detectar PK: si la tabla tiene una columna id* la capturamos; si no, empaquetamos toda la PK
                -- A) Intento de PK simple por convención: primera columna que empiece con 'id' y esté en OLD/NEW
                IF (v_op = 'INSERT') THEN
                    v_id := (SELECT to_jsonb(NEW)->>key
                             FROM jsonb_object_keys(to_jsonb(NEW)) AS key
                             WHERE key LIKE 'id%'\n
                             ORDER BY length(key) ASC LIMIT 1);
                ELSIF (v_op = 'UPDATE' OR v_op = 'DELETE') THEN
                    v_id := (SELECT to_jsonb(COALESCE(NEW, OLD))->>key
                             FROM jsonb_object_keys(to_jsonb(COALESCE(NEW, OLD))) AS key
                             WHERE key LIKE 'id%'\n
                             ORDER BY length(key) ASC LIMIT 1);
                END IF;

                -- B) Si no hay una PK simple detectable, guardamos la(s) clave(s) como JSON
                -- Nota: pg no conoce la PK declarada aquí sin consultar catálogo; simplificamos:
                IF v_id IS NULL THEN
                    IF v_op = 'INSERT' THEN
                        v_pk := to_jsonb(NEW);
                    ELSIF v_op = 'UPDATE' THEN
                        v_pk := to_jsonb(NEW);
                    ELSE
                        v_pk := to_jsonb(OLD);
                    END IF;
                END IF;

                INSERT INTO bitacora(tabla, operacion, idregistro, pk_json, datos_old, datos_new, db_usuario, ip_cliente)
                VALUES (
                    v_tabla,
                    v_op,
                    v_id,
                    v_pk,
                    CASE WHEN v_op IN ('UPDATE','DELETE') THEN to_jsonb(OLD) ELSE NULL END,
                    CASE WHEN v_op IN ('INSERT','UPDATE') THEN to_jsonb(NEW) ELSE NULL END,
                    SESSION_USER,
                    inet_client_addr()
                );

                -- Para INSERT/UPDATE devolvemos NEW; para DELETE devolvemos OLD
                IF (v_op = 'DELETE') THEN
                    RETURN OLD;
                ELSE
                    RETURN NEW;
                END IF;
            END;
            $$ LANGUAGE plpgsql SECURITY DEFINER;
        ");

        // 4) Adjuntar triggers a las tablas que te interesan (agrega o quita según tu dominio)
        foreach ([
            'usuario',
            'coordinador',
            'docente',
            'rol',
            'permiso',
            'rol_permiso',
            'facultad',
            'aula',
            'carrera',
            'gestion',
            'materia',
            'materia_carrera',
            'horario'
        ] as $tabla) {

            // Nombre único de trigger por tabla
            $tg = 'trg_bitacora_' . $tabla;

            // Borrar si existiera
            DB::statement("DROP TRIGGER IF EXISTS {$tg}_ins ON {$tabla};");
            DB::statement("DROP TRIGGER IF EXISTS {$tg}_upd ON {$tabla};");
            DB::statement("DROP TRIGGER IF EXISTS {$tg}_del ON {$tabla};");

            // Crear triggers AFTER para capturar NEW/OLD definitivos
            DB::statement("
                CREATE TRIGGER {$tg}_ins
                AFTER INSERT ON {$tabla}
                FOR EACH ROW EXECUTE FUNCTION fn_log_bitacora();
            ");
            DB::statement("
                CREATE TRIGGER {$tg}_upd
                AFTER UPDATE ON {$tabla}
                FOR EACH ROW EXECUTE FUNCTION fn_log_bitacora();
            ");
            DB::statement("
                CREATE TRIGGER {$tg}_del
                AFTER DELETE ON {$tabla}
                FOR EACH ROW EXECUTE FUNCTION fn_log_bitacora();
            ");
        }
    }

    public function down(): void
    {
        // Quitar triggers
        foreach ([
            'usuario',
            'coordinador',
            'docente',
            'rol',
            'permiso',
            'rol_permiso',
            'facultad',
            'aula',
            'carrera',
            'gestion',
            'materia',
            'materia_carrera',
            'horario'
        ] as $tabla) {
            $tg = 'trg_bitacora_' . $tabla;
            DB::statement("DROP TRIGGER IF EXISTS {$tg}_ins ON {$tabla};");
            DB::statement("DROP TRIGGER IF EXISTS {$tg}_upd ON {$tabla};");
            DB::statement("DROP TRIGGER IF EXISTS {$tg}_del ON {$tabla};");
        }

        // Quitar función
        DB::statement("DROP FUNCTION IF EXISTS fn_log_bitacora();");

        // Quitar tabla
        Schema::dropIfExists('bitacora');
    }
};
