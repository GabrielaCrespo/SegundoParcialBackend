<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facultad', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->timestamps();
        });

        Schema::create('carrera', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->unsignedBigInteger('idfacultad');
            $table->timestamps();

            $table->foreign('idfacultad')->references('id')->on('facultad')->cascadeOnDelete();
            $table->unique(['nombre','idfacultad']);
        });

        Schema::create('aula', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50);
            $table->unsignedBigInteger('idfacultad');
            $table->integer('capacidad')->default(40);
            $table->timestamps();

            $table->foreign('idfacultad')->references('id')->on('facultad')->cascadeOnDelete();
            $table->unique(['codigo','idfacultad']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('aula');
        Schema::dropIfExists('carrera');
        Schema::dropIfExists('facultad');
    }
};
