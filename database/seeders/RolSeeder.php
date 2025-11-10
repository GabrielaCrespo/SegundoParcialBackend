<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso completo'],
            ['nombre' => 'Coordinador',  'descripcion' => 'Gestiona carreras y materias'],
            ['nombre' => 'Docente',      'descripcion' => 'Acceso a horarios y asistencia'],
        ];

        foreach ($rows as $r) {
            DB::table('rol')->updateOrInsert(
                ['nombre' => $r['nombre']],
                ['descripcion' => $r['descripcion']]
            );
        }
    }
}
