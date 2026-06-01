<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencias', function (Blueprint $table) {
            if (Schema::hasColumn('asistencias', 'id_supervisor')) {
                $table->dropForeign('asistencia_supervisor_id');
                $table->dropColumn('id_supervisor');
            }
            if (Schema::hasColumn('asistencias', 'motivo_rechazo')) {
                $table->dropColumn('motivo_rechazo');
            }
        });

        Schema::dropIfExists('bitacora_asistencias');
    }

    public function down(): void
    {
    }
};
