<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('horario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idmateria');
            $table->unsignedBigInteger('idaula');
            $table->unsignedBigInteger('iddocente')->nullable();
            $table->string('dia', 2);     // 'LU','MA','MI','JU','VI','SA'
            $table->time('horainicio');   // si prefieres timeTz(), cámbialo aquí y en tu código
            $table->time('horafinal');
            $table->timestamps();

            $table->foreign('idmateria')->references('id')->on('materia')->cascadeOnDelete();
            $table->foreign('idaula')->references('id')->on('aula')->restrictOnDelete();
            $table->foreign('iddocente')->references('id')->on('docente')->nullOnDelete();
        });

        DB::statement("ALTER TABLE horario
            ADD CONSTRAINT chk_dia CHECK (dia IN ('LU','MA','MI','JU','VI','SA'));");
        DB::statement("ALTER TABLE horario
            ADD CONSTRAINT chk_hora CHECK (horainicio < horafinal);");
    }
    public function down(): void {
        Schema::dropIfExists('horario');
    }
};
