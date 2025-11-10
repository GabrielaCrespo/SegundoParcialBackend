    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up(): void {
            Schema::create('usuario', function (Blueprint $table) {
                $table->increments('idusuario');
                $table->string('nombre', 150);
                $table->string('celular', 30)->nullable();
                $table->string('username', 100)->unique();
                $table->string('email', 150)->unique();
                $table->string('password', 255);
                $table->boolean('activo')->default(true);
                $table->unsignedInteger('idrol');

                $table->foreign('idrol')->references('idrol')->on('rol')->restrictOnDelete();
            });
        }
        public function down(): void {
            Schema::dropIfExists('usuario');
        }
    };
