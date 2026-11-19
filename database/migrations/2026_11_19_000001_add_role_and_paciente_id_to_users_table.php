<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('paciente');
            $table->uuid('paciente_id')->nullable()->index();
        });

        // SQLite no permite agregar una llave foránea a una tabla existente.
        // En PostgreSQL/MySQL se agrega la restricción real.
        if (DB::connection()->getDriverName() !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('paciente_id')
                    ->references('id')
                    ->on('pacientes')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['paciente_id']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'paciente_id']);
        });
    }
};
