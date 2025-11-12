<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('session_tokens', function (Blueprint $table) {
            $table->id('idsession');
            $table->unsignedBigInteger('idusuario');
            $table->string('nombre');
            $table->string('email');
            $table->string('token', 512);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_tokens');
    }
};
