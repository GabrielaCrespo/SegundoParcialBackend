<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gestion', function (Blueprint $table) {
            $table->increments('idgestion');
            $table->integer('anio');
            $table->string('periodo', 20); // p.ej. 'I','II' u otro literal que usa tu app
            $table->date('fechainicio')->nullable();
            $table->date('fechafin')->nullable();
        });
    }
    public function down(): void {
        Schema::dropIfExists('gestion');
    }
};
