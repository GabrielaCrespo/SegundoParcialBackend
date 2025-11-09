<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('rol')->where('nombre','Administrador')->value('id');
        $permIds = DB::table('permiso')->pluck('id')->all();

        $rows = [];
        $now = now();
        foreach ($permIds as $pid) {
            $rows[] = ['idrol'=>$adminId,'idpermiso'=>$pid,'created_at'=>$now,'updated_at'=>$now];
        }
        DB::table('rol_permiso')->insert($rows);
    }
}
