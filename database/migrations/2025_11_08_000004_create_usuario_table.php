<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idrol');
            $table->string('nombre', 120);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('idrol')->references('id')->on('rol')->restrictOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('usuario');
    }
};
