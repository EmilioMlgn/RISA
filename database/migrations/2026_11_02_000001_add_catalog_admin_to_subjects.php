<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Activa / Inactiva (baja lógica). Las materias inactivas salen de la retícula vigente.
            $table->string('status', 10)->default('Activa')->index()->after('area');
        });

        Schema::create('catalog_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 255);
            $table->string('user_role', 50);
            $table->string('action', 60);
            $table->text('detail');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_audit_logs');
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
