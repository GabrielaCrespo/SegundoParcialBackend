<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $horarios = [
            // Lunes a viernes, 3 franjas horarias
            ['dia' => 'LU', 'horainicio' => '08:00', 'horafinal' => '10:00'],
            ['dia' => 'LU', 'horainicio' => '10:00', 'horafinal' => '12:00'],
            ['dia' => 'LU', 'horainicio' => '14:00', 'horafinal' => '16:00'],
            ['dia' => 'MA', 'horainicio' => '08:00', 'horafinal' => '10:00'],
            ['dia' => 'MA', 'horainicio' => '10:00', 'horafinal' => '12:00'],
            ['dia' => 'MA', 'horainicio' => '14:00', 'horafinal' => '16:00'],
            ['dia' => 'MI', 'horainicio' => '08:00', 'horafinal' => '10:00'],
            ['dia' => 'MI', 'horainicio' => '10:00', 'horafinal' => '12:00'],
            ['dia' => 'MI', 'horainicio' => '14:00', 'horafinal' => '16:00'],
            ['dia' => 'JU', 'horainicio' => '08:00', 'horafinal' => '10:00'],
            ['dia' => 'JU', 'horainicio' => '10:00', 'horafinal' => '12:00'],
            ['dia' => 'JU', 'horainicio' => '14:00', 'horafinal' => '16:00'],
            ['dia' => 'VI', 'horainicio' => '08:00', 'horafinal' => '10:00'],
            ['dia' => 'VI', 'horainicio' => '10:00', 'horafinal' => '12:00'],
            ['dia' => 'VI', 'horainicio' => '14:00', 'horafinal' => '16:00'],
        ];

        foreach ($horarios as $horario) {
            DB::table('horario')->insert($horario);
        }
    }
}
