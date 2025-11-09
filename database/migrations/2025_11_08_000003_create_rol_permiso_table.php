<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idrol');
            $table->unsignedBigInteger('idpermiso');
            $table->timestamps();

            $table->unique(['idrol','idpermiso']);
            $table->foreign('idrol')->references('id')->on('rol')->cascadeOnDelete();
            $table->foreign('idpermiso')->references('id')->on('permiso')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rol_permiso');
    }
};
