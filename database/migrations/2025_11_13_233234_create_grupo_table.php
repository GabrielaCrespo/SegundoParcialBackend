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
        Schema::create('grupo', function (Blueprint $table) {
            $table->increments('idgrupo');
            $table->string('nombre_grupo', 20);
            $table->integer('idmateria')->unsigned();
            $table->integer('idgestion')->unsigned();
            $table->integer('capacidad')->default(30)->nullable();
            
            // Foreign keys
            $table->foreign('idmateria')->references('idmateria')->on('materia')->onDelete('cascade');
            $table->foreign('idgestion')->references('idgestion')->on('gestion')->onDelete('cascade');
            
            // Constraint único: no puede haber dos grupos con el mismo nombre para la misma materia en la misma gestión
            $table->unique(['idmateria', 'idgestion', 'nombre_grupo'], 'grupo_materia_gestion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo');
    }
};
