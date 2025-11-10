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
            ['nombre'=>'usuarios.view','descripcion'=>'Ver usuarios'],
            ['nombre'=>'usuarios.create','descripcion'=>'Crear usuarios'],
            ['nombre'=>'usuarios.update','descripcion'=>'Editar usuarios'],
            ['nombre'=>'usuarios.delete','descripcion'=>'Eliminar usuarios'],

            // Roles y permisos
            ['nombre'=>'roles.view','descripcion'=>'Ver roles'],
            ['nombre'=>'roles.create','descripcion'=>'Crear roles'],
            ['nombre'=>'roles.update','descripcion'=>'Editar roles'],
            ['nombre'=>'roles.delete','descripcion'=>'Eliminar roles'],
            ['nombre'=>'roles.assign_permiso','descripcion'=>'Asignar permisos a rol'],

            // Materias
            ['nombre'=>'materias.view','descripcion'=>'Ver materias'],
            ['nombre'=>'materias.create','descripcion'=>'Crear materias'],
            ['nombre'=>'materias.update','descripcion'=>'Editar materias'],
            ['nombre'=>'materias.delete','descripcion'=>'Eliminar materias'],

            // Carreras
            ['nombre'=>'carreras.view','descripcion'=>'Ver carreras'],
            ['nombre'=>'carreras.create','descripcion'=>'Crear carreras'],
            ['nombre'=>'carreras.update','descripcion'=>'Editar carreras'],
            ['nombre'=>'carreras.delete','descripcion'=>'Eliminar carreras'],

            // Aulas
            ['nombre'=>'aulas.view','descripcion'=>'Ver aulas'],
            ['nombre'=>'aulas.create','descripcion'=>'Crear aulas'],
            ['nombre'=>'aulas.update','descripcion'=>'Editar aulas'],
            ['nombre'=>'aulas.delete','descripcion'=>'Eliminar aulas'],

            // Docentes
            ['nombre'=>'docentes.view','descripcion'=>'Ver docentes'],
            ['nombre'=>'docentes.create','descripcion'=>'Crear docentes'],
            ['nombre'=>'docentes.update','descripcion'=>'Editar docentes'],
            ['nombre'=>'docentes.delete','descripcion'=>'Eliminar docentes'],

            // Coordinadores
            ['nombre'=>'coordinadores.view','descripcion'=>'Ver coordinadores'],
            ['nombre'=>'coordinadores.create','descripcion'=>'Crear coordinadores'],
            ['nombre'=>'coordinadores.update','descripcion'=>'Editar coordinadores'],
            ['nombre'=>'coordinadores.delete','descripcion'=>'Eliminar coordinadores'],

            // Horarios
            ['nombre'=>'horarios.view','descripcion'=>'Ver horarios'],
            ['nombre'=>'horarios.create','descripcion'=>'Crear horarios'],
            ['nombre'=>'horarios.update','descripcion'=>'Editar horarios'],
            ['nombre'=>'horarios.delete','descripcion'=>'Eliminar horarios'],
        ];

        foreach ($permisos as $p) {
            DB::table('permiso')->updateOrInsert(
                ['nombre' => $p['nombre']],
                ['descripcion' => $p['descripcion']]
            );
        }
    }
}
