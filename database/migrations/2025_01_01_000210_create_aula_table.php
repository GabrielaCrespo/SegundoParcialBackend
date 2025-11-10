<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aula', function (Blueprint $table) {
            $table->increments('idaula');
            $table->string('numero', 50)->unique(); // el modelo valida unicidad
            $table->string('tipo', 50)->nullable();
            $table->unsignedInteger('idfacultad')->nullable();

            $table->foreign('idfacultad')->references('idfacultad')->on('facultad')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('aula');
    }
};
