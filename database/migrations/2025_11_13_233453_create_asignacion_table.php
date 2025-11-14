<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('asignacion', function (Blueprint $table) {
            $table->increments('idasignacion');
            $table->unsignedInteger('idgrupo');
            $table->unsignedInteger('idmateria');
            $table->unsignedInteger('iddocente');
            $table->unsignedInteger('idaula');
            $table->unsignedInteger('idgestion');
            $table->timestamp('fecha_creacion')->useCurrent();

            // Foreign keys
            $table->foreign('idgrupo')
                  ->references('idgrupo')
                  ->on('grupo')
                  ->cascadeOnDelete();
            
            $table->foreign('idmateria')
                  ->references('idmateria')
                  ->on('materia')
                  ->restrictOnDelete();
            
            $table->foreign('iddocente')
                  ->references('iddocente')
                  ->on('docente')
                  ->restrictOnDelete();
            
            $table->foreign('idaula')
                  ->references('idaula')
                  ->on('aula')
                  ->restrictOnDelete();
            
            $table->foreign('idgestion')
                  ->references('idgestion')
                  ->on('gestion')
                  ->cascadeOnDelete();

            // Un grupo solo puede tener una asignación de materia/docente/aula
            $table->unique(['idgrupo', 'idmateria','iddocente','idaula','idgestion'], 'asignacion_grupo_materia_unique');
        });

        // Crear índices para mejorar consultas
        Schema::table('asignacion', function (Blueprint $table) {
            $table->index('idgestion', 'idx_asignacion_gestion');
            $table->index('iddocente', 'idx_asignacion_docente');
            $table->index('idaula', 'idx_asignacion_aula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion');
    }
};
