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
        Schema::table('horarios', function (Blueprint $table) {
            $table->renameColumn('hora_entrada_esperada', 'hora_entrada_tolerada');
            $table->renameColumn('hora_salida_esperada', 'hora_salida_tolerada');
            $table->string('hora_entrada')->nullable()->after('dia');
            $table->string('hora_salida')->nullable()->after('hora_entrada');
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropColumn(['hora_entrada', 'hora_salida']);
            $table->renameColumn('hora_entrada_tolerada', 'hora_entrada_esperada');
            $table->renameColumn('hora_salida_tolerada', 'hora_salida_esperada');
        });
    }
};
