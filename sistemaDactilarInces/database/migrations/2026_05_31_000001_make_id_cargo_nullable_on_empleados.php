<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign('empleados_cargo_id');
            $table->dropIndex('empleados_cargo_id');
            $table->unsignedBigInteger('id_cargo')->nullable()->change();
            $table->foreign('id_cargo', 'empleados_cargo_id')
                ->references('id')
                ->on('cargos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign('empleados_cargo_id');
            $table->unsignedBigInteger('id_cargo')->nullable(false)->change();
            $table->foreign('id_cargo', 'empleados_cargo_id')
                ->references('id')
                ->on('cargos');
        });
    }
};
