<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->enum('status', ['presente', 'pending_approval', 'approved', 'rejected'])
                ->default('presente')
                ->after('hora_salida');
            $table->enum('tipo_marcacion', ['kiosko', 'manual'])
                ->default('kiosko')
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn(['status', 'tipo_marcacion']);
        });
    }
};
