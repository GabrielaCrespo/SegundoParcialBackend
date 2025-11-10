<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facultad', function (Blueprint $table) {
            $table->increments('idfacultad');
            $table->string('nombre', 150);
            $table->integer('nro')->unique(); // usado en JOINs y listados
        });
    }
    public function down(): void {
        Schema::dropIfExists('facultad');
    }
};
