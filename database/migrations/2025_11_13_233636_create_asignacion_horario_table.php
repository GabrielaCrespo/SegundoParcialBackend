<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void {
        Schema::create('asignacion_horario', function (Blueprint $table) {
            $table->increments('idasignacionhorario');
            $table->unsignedInteger('idasignacion');
            $table->unsignedInteger('idhorario');

            // Foreign keys
            $table->foreign('idasignacion')
                  ->references('idasignacion')
                  ->on('asignacion')
                  ->cascadeOnDelete();
            
            $table->foreign('idhorario')
                  ->references('idhorario')
                  ->on('horario')
                  ->restrictOnDelete();

            // Un horario no puede ser asignado dos veces a la misma asignación
            $table->unique(['idasignacion', 'idhorario'], 'asignacion_horario_unique');
        });

        // Crear índice para búsquedas rápidas de conflictos
        Schema::table('asignacion_horario', function (Blueprint $table) {
            $table->index('idhorario', 'idx_asignacion_horario_horario');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_horario');
    }
};
