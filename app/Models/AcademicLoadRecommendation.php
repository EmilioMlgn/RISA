<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicLoadRecommendation extends Model
{
    use HasFactory;

    protected $table = 'academic_load_recommendations';

    protected $fillable = [
        'user_id',
        'period_target',
        'total_recommended_credits',
        'projected_semesters_remaining',
        'recommended_subjects_payload',
        'diagnostic_notes',
    ];

    protected function casts(): array
    {
        return [
            'total_recommended_credits' => 'integer',
            'projected_semesters_remaining' => 'integer',
            'recommended_subjects_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
