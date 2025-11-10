<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('carrera', function (Blueprint $table) {
            $table->increments('idcarrera');
            $table->string('nombre', 150);
            $table->string('sigla', 20)->unique();
            $table->unsignedInteger('idfacultad')->nullable();

            $table->foreign('idfacultad')->references('idfacultad')->on('facultad')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('carrera');
    }
};
