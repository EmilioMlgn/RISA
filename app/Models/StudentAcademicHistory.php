<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAcademicHistory extends Model
{
    use HasFactory;

    protected $table = 'student_academic_histories';

    protected $fillable = [
        'user_id',
        'subject_id',
        'status',
        'grade',
        'attempts',
        'period',
    ];

    protected function casts(): array
    {
        return [
            'grade' => 'float',
            'attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
