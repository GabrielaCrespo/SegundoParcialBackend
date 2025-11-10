<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Para Postgres, mejor CHECK que enum de MySQL
        Schema::create('horario', function (Blueprint $table) {
            $table->increments('idhorario');
            $table->string('dia', 2); // 'LU','MA','MI','JU','VI','SA'
            $table->time('horainicio');
            $table->time('horafinal');
        });

        DB::statement("ALTER TABLE horario ADD CONSTRAINT horario_dia_chk CHECK (dia IN ('LU','MA','MI','JU','VI','SA'))");
    }
    public function down(): void {
        Schema::dropIfExists('horario');
    }
};
