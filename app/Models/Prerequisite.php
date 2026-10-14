<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prerequisite extends Model
{
    use HasFactory;

    protected $table = 'prerequisites';

    protected $fillable = [
        'subject_id',
        'prerequisite_subject_id',
        'type',
        'required_credits',
    ];

    protected function casts(): array
    {
        return [
            'required_credits' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function prerequisiteSubject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'prerequisite_subject_id');
    }
}
