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
        // 1. Catálogo de Planes de Estudio (e.g. ISC-2010 TecNM)
        Schema::create('study_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            $table->string('institution', 255)->default('Instituto Tecnológico de Celaya');
            $table->unsignedSmallInteger('total_credits')->default(260);
            $table->unsignedSmallInteger('total_semesters')->default(9);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Catálogo de Materias / Asignaturas
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_plan_id')->constrained('study_plans')->cascadeOnDelete();
            $table->string('code', 50)->index();
            $table->string('name', 255);
            $table->unsignedSmallInteger('credits');
            $table->unsignedSmallInteger('theory_hours')->default(2);
            $table->unsignedSmallInteger('practice_hours')->default(3);
            $table->unsignedSmallInteger('semester_order')->index(); // Semestre 1 a 9
            $table->string('area', 100)->nullable(); // Ciencias Básicas, Programación, Redes, etc.
            $table->timestamps();
        });

        // 3. Tabla de Prerrequisitos (Seriación curricular y grafo de dependencias)
        Schema::create('prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete(); // Materia que requiere el requisito
            $table->foreignId('prerequisite_subject_id')->nullable()->constrained('subjects')->cascadeOnDelete(); // Materia requerida
            $table->string('type', 30)->default('obligatorio'); // obligatorio, correquisito, creditos_minimos
            $table->unsignedSmallInteger('required_credits')->default(0); // Para casos como Residencia o Servicio
            $table->timestamps();

            $table->index(['subject_id', 'prerequisite_subject_id']);
        });

        // 4. Historial Académico de Alumnos por Asignatura
        Schema::create('student_academic_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->char('status', 1)->default('P')->index(); // A: Aprobada, C: Cursando, R: Reprobada, D: Disponible, P: Pendiente
            $table->decimal('grade', 5, 2)->nullable(); // 0 a 100
            $table->unsignedSmallInteger('attempts')->default(1); // Número de veces cursada
            $table->string('period', 30)->nullable(); // e.g. '2026-1'
            $table->timestamps();

            $table->unique(['user_id', 'subject_id']);
        });

        // 5. Comentarios y Observaciones de Docentes / Asesores
        Schema::create('subject_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Alumno al que pertenece la observación
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete(); // Materia observada
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete(); // Docente o asesor
            $table->string('author_name', 255);
            $table->string('author_role', 100)->default('Docente'); // Docente, Tutor, Asesor Académico
            $table->text('comment');
            $table->timestamps();

            $table->index(['user_id', 'subject_id']);
        });

        // 6. Registro de Recomendaciones de Carga Académica generadas por el Autómata
        Schema::create('academic_load_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_target', 30); // Periodo sugerido (e.g. 2026-2)
            $table->unsignedSmallInteger('total_recommended_credits');
            $table->unsignedSmallInteger('projected_semesters_remaining');
            $table->jsonb('recommended_subjects_payload'); // Desglose JSON de materias sugeridas y su prioridad
            $table->text('diagnostic_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_load_recommendations');
        Schema::dropIfExists('subject_comments');
        Schema::dropIfExists('student_academic_histories');
        Schema::dropIfExists('prerequisites');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('study_plans');
    }
};
