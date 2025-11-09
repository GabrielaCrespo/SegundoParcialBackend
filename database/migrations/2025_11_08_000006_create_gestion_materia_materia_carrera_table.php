<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gestion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique(); // 2025-1, 2025-2
            $table->timestamps();
        });

        Schema::create('materia', function (Blueprint $table) {
            $table->id();
            $table->string('sigla', 20);
            $table->string('nombre', 150);
            $table->unsignedBigInteger('idgestion');
            $table->timestamps();

            $table->foreign('idgestion')->references('id')->on('gestion')->restrictOnDelete();
            $table->unique(['sigla','idgestion']);
        });

        Schema::create('materia_carrera', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idmateria');
            $table->unsignedBigInteger('idcarrera');
            $table->timestamps();

            $table->unique(['idmateria','idcarrera']);
            $table->foreign('idmateria')->references('id')->on('materia')->cascadeOnDelete();
            $table->foreign('idcarrera')->references('id')->on('carrera')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('materia_carrera');
        Schema::dropIfExists('materia');
        Schema::dropIfExists('gestion');
    }
};
