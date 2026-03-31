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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_cargo')->constrained(
                table: 'cargos',
                indexName: 'empleados_cargo_id'
            );
            $table->string('nombre');
            $table->string('apellido');
            $table->string('telefono');
            $table->string('identificacion');
            $table->string('correo');
            $table->string('contraseña');
            $table->string('foto');
            $table->string('sexo');
            $table->string('huella_pulgar');
            $table->string('huella_indice');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
