<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            // Usuarios
            ['codigo'=>'usuarios.view','descripcion'=>'Ver usuarios'],
            ['codigo'=>'usuarios.create','descripcion'=>'Crear usuarios'],
            ['codigo'=>'usuarios.update','descripcion'=>'Editar usuarios'],
            ['codigo'=>'usuarios.delete','descripcion'=>'Eliminar usuarios'],

            // Roles y permisos
            ['codigo'=>'roles.view','descripcion'=>'Ver roles'],
            ['codigo'=>'roles.create','descripcion'=>'Crear roles'],
            ['codigo'=>'roles.update','descripcion'=>'Editar roles'],
            ['codigo'=>'roles.delete','descripcion'=>'Eliminar roles'],
            ['codigo'=>'roles.assign_permiso','descripcion'=>'Asignar permisos a rol'],

            // Materias
            ['codigo'=>'materias.view','descripcion'=>'Ver materias'],
            ['codigo'=>'materias.create','descripcion'=>'Crear materias'],
            ['codigo'=>'materias.update','descripcion'=>'Editar materias'],
            ['codigo'=>'materias.delete','descripcion'=>'Eliminar materias'],

            // Carreras
            ['codigo'=>'carreras.view','descripcion'=>'Ver carreras'],
            ['codigo'=>'carreras.create','descripcion'=>'Crear carreras'],
            ['codigo'=>'carreras.update','descripcion'=>'Editar carreras'],
            ['codigo'=>'carreras.delete','descripcion'=>'Eliminar carreras'],

            // Aulas
            ['codigo'=>'aulas.view','descripcion'=>'Ver aulas'],
            ['codigo'=>'aulas.create','descripcion'=>'Crear aulas'],
            ['codigo'=>'aulas.update','descripcion'=>'Editar aulas'],
            ['codigo'=>'aulas.delete','descripcion'=>'Eliminar aulas'],

            // Docentes
            ['codigo'=>'docentes.view','descripcion'=>'Ver docentes'],
            ['codigo'=>'docentes.create','descripcion'=>'Crear docentes'],
            ['codigo'=>'docentes.update','descripcion'=>'Editar docentes'],
            ['codigo'=>'docentes.delete','descripcion'=>'Eliminar docentes'],

            // Coordinadores
            ['codigo'=>'coordinadores.view','descripcion'=>'Ver coordinadores'],
            ['codigo'=>'coordinadores.create','descripcion'=>'Crear coordinadores'],
            ['codigo'=>'coordinadores.update','descripcion'=>'Editar coordinadores'],
            ['codigo'=>'coordinadores.delete','descripcion'=>'Eliminar coordinadores'],

            // Horarios
            ['codigo'=>'horarios.view','descripcion'=>'Ver horarios'],
            ['codigo'=>'horarios.create','descripcion'=>'Crear horarios'],
            ['codigo'=>'horarios.update','descripcion'=>'Editar horarios'],
            ['codigo'=>'horarios.delete','descripcion'=>'Eliminar horarios'],
        ];

        $now = now();
        foreach ($permisos as &$p) { $p['created_at']=$now; $p['updated_at']=$now; }
        DB::table('permiso')->insert($permisos);
    }
}
