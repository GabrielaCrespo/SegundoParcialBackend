<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('rol')->where('nombre','Administrador')->value('idrol');
        if (!$adminId) {
            throw new \RuntimeException("No existe el rol 'Administrador'. Ejecuta primero RolSeeder.");
        }

        $permIds = DB::table('permiso')->pluck('idpermiso')->all();

        $rows = [];
        foreach ($permIds as $pid) {
            $rows[] = ['idrol' => $adminId, 'idpermiso' => $pid];
        }

        // Evita error si lo corres varias veces
        if (!empty($rows)) {
            DB::table('rol_permiso')->insertOrIgnore($rows);
        }
    }
}
