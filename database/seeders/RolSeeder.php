<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rol')->insert([
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso completo', 'created_at'=>now(),'updated_at'=>now()],
            ['nombre' => 'Coordinador',  'descripcion' => 'Gestiona carreras y materias', 'created_at'=>now(),'updated_at'=>now()],
            ['nombre' => 'Docente',      'descripcion' => 'Acceso a horarios y asistencia', 'created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
