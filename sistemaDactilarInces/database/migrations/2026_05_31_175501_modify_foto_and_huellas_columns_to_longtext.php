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
        Schema::table('empleados', function (Blueprint $table) {
            $table->longText('foto')->change();
            $table->longText('huella_pulgar')->change();
            $table->longText('huella_indice')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('foto')->change();
            $table->string('huella_pulgar')->change();
            $table->string('huella_indice')->change();
        });
    }
};
