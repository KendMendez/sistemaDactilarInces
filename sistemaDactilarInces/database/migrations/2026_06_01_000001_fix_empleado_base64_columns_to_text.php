<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->text('huella_pulgar')->nullable()->change();
            $table->text('huella_indice')->nullable()->change();
            $table->text('foto')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('huella_pulgar')->nullable()->change();
            $table->string('huella_indice')->nullable()->change();
            $table->string('foto')->nullable()->change();
        });
    }
};
