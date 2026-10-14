<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'status' => ['required', 'in:A,C,R,D,P'],
            'grade' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'attempts' => ['nullable', 'integer', 'min:1', 'max:10'],
            'student_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Custom error messages in Spanish.
     */
    public function messages(): array
    {
        return [
            'subject_id.required' => 'La asignatura es obligatoria.',
            'status.required' => 'El estatus académico es obligatorio.',
            'status.in' => 'El estatus debe ser uno de los permitidos (A, C, R, D, P).',
            'grade.numeric' => 'La calificación debe ser un valor numérico.',
            'grade.min' => 'La calificación mínima es 0.',
            'grade.max' => 'La calificación máxima es 100.',
        ];
    }
}
