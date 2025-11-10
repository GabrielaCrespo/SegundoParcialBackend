<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('rol')->where('nombre', 'Administrador')->value('idrol');

        if (!$adminId) {
            throw new \RuntimeException("No existe el rol 'Administrador'. Ejecuta primero RolSeeder.");
        }

        DB::table('usuario')->updateOrInsert(
            ['email' => 'ronny0ronaldo@gmail.com'], // clave única
            [
                'nombre'   => 'Ronny Ronaldo Choque Cabana', // ← nombre completo, más claro
                'celular'  => '72687933',
                'username' => 'Dante', // ← login único
                'password' => Hash::make('Ronny13210610'),
                'activo'   => true,
                'idrol'    => $adminId,
            ]
        );
    }
}

