<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('rol')->where('nombre','Administrador')->value('id');

        DB::table('usuario')->insert([
            'nombre' => 'Dante',
            'email' => 'ronny0ronaldo@gmail.com',
            'password' => Hash::make('Ronny13210610'),
            'idrol' => $adminId,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
