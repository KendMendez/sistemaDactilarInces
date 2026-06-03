<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->longText('foto')->nullable()->change();
            $table->longText('huella_pulgar')->nullable()->change();
            $table->longText('huella_indice')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->text('foto')->nullable()->change();
            $table->text('huella_pulgar')->nullable()->change();
            $table->text('huella_indice')->nullable()->change();
        });
    }
};
