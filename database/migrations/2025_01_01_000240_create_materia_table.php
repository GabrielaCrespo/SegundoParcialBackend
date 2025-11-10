<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('materia', function (Blueprint $table) {
            $table->increments('idmateria');
            $table->string('nombre', 150);
            $table->string('sigla', 20)->unique();
            $table->integer('semestre')->default(1);
            $table->unsignedInteger('idgestion');

            $table->foreign('idgestion')->references('idgestion')->on('gestion')->restrictOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('materia');
    }
};
