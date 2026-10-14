<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'control_number',
        'career',
        'current_semester',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'current_semester' => 'integer',
        ];
    }

    /**
     * Historial académico del estudiante.
     */
    public function academicHistories(): HasMany
    {
        return $this->hasMany(StudentAcademicHistory::class);
    }

    /**
     * Comentarios y observaciones recibidas.
     */
    public function subjectComments(): HasMany
    {
        return $this->hasMany(SubjectComment::class);
    }

    /**
     * Recomendaciones de carga generadas para el alumno.
     */
    public function loadRecommendations(): HasMany
    {
        return $this->hasMany(AcademicLoadRecommendation::class);
    }
}
