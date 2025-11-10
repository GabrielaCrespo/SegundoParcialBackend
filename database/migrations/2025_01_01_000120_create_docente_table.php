<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('docente', function (Blueprint $table) {
            // PK = FK a usuario (herencia 1:1)
            $table->unsignedInteger('iddocente')->primary();
            $table->string('especialidad', 150)->nullable();
            $table->date('fechacontrato')->nullable();

            $table->foreign('iddocente')
                  ->references('idusuario')->on('usuario')
                  ->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('docente');
    }
};
