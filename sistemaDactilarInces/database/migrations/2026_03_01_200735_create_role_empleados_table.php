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
        Schema::create('role_empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empleado')->constrained(
                table: 'empleados'
            );
            $table->foreignId('id_role')->constrained(
                table: 'roles'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_empleados');
    }
};
