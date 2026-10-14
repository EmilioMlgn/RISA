<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyPlan extends Model
{
    use HasFactory;

    protected $table = 'study_plans';

    protected $fillable = [
        'code',
        'name',
        'institution',
        'total_credits',
        'total_semesters',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'total_credits' => 'integer',
            'total_semesters' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Materias que componen el plan de estudios.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('semester_order')->orderBy('id');
    }
}
