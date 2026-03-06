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
        Schema::create('role_privilegios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_role')->constrained(
                table: 'roles',
                indexName: 'role_id'
            );
            $table->foreignId('id_privilegio')->constrained(
                table: 'privilegios',
                indexName: 'privilegio_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_privilegios');
    }
};
