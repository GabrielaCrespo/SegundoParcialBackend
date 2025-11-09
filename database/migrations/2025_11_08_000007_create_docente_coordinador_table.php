<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('docente', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('email', 150)->nullable();
            $table->string('especialidad', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('coordinador', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('email', 150)->nullable();
            $table->unsignedBigInteger('idcarrera')->nullable();
            $table->timestamps();

            $table->foreign('idcarrera')->references('id')->on('carrera')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('coordinador');
        Schema::dropIfExists('docente');
    }
};
