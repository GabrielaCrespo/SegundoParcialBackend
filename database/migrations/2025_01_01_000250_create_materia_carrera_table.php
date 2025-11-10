<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('materia_carrera', function (Blueprint $table) {
            $table->unsignedInteger('idmateria');
            $table->unsignedInteger('idcarrera');
            $table->primary(['idmateria','idcarrera']);

            $table->foreign('idmateria')->references('idmateria')->on('materia')->cascadeOnDelete();
            $table->foreign('idcarrera')->references('idcarrera')->on('carrera')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('materia_carrera');
    }
};
