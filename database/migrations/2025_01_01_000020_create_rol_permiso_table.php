<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->unsignedInteger('idrol');
            $table->unsignedInteger('idpermiso');
            $table->primary(['idrol','idpermiso']);

            $table->foreign('idrol')->references('idrol')->on('rol')->onDelete('cascade');
            $table->foreign('idpermiso')->references('idpermiso')->on('permiso')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('rol_permiso');
    }
};

