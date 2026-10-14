<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subjects';

    protected $fillable = [
        'study_plan_id',
        'code',
        'name',
        'credits',
        'theory_hours',
        'practice_hours',
        'semester_order',
        'area',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'theory_hours' => 'integer',
            'practice_hours' => 'integer',
            'semester_order' => 'integer',
        ];
    }

    /**
     * Plan de estudios al que pertenece la asignatura.
     */
    public function studyPlan(): BelongsTo
    {
        return $this->belongsTo(StudyPlan::class);
    }

    /**
     * Registros de prerrequisitos donde esta materia es la dependiente.
     */
    public function prerequisiteEntries(): HasMany
    {
        return $this->hasMany(Prerequisite::class, 'subject_id');
    }

    /**
     * Materias que deben cursarse/aprobarse antes de esta.
     */
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'prerequisites',
            'subject_id',
            'prerequisite_subject_id'
        )->withPivot(['type', 'required_credits'])->withTimestamps();
    }

    /**
     * Materias que requieren a esta materia como prerrequisito (materias subsecuentes).
     */
    public function unlockedSubjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'prerequisites',
            'prerequisite_subject_id',
            'subject_id'
        )->withPivot(['type', 'required_credits'])->withTimestamps();
    }

    /**
     * Historiales académicos asociados a esta materia.
     */
    public function academicHistories(): HasMany
    {
        return $this->hasMany(StudentAcademicHistory::class);
    }

    /**
     * Comentarios y observaciones de docentes.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(SubjectComment::class);
    }
}
