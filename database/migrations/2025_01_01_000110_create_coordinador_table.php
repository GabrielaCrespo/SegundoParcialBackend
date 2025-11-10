<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('coordinador', function (Blueprint $table) {
            // PK = FK a usuario (herencia 1:1)
            $table->unsignedInteger('idcoordinador')->primary();
            $table->date('fechacontrato')->nullable();

            $table->foreign('idcoordinador')
                  ->references('idusuario')->on('usuario')
                  ->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('coordinador');
    }
};
