<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectCommentRequest extends FormRequest
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
            'author_name' => ['required', 'string', 'max:255'],
            'author_role' => ['required', 'string', 'max:100'],
            'comment' => ['required', 'string', 'min:5', 'max:2000'],
            'student_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Custom error messages in Spanish.
     */
    public function messages(): array
    {
        return [
            'author_name.required' => 'El nombre del docente o asesor es obligatorio.',
            'author_role.required' => 'El rol institucional es obligatorio.',
            'comment.required' => 'El comentario u observación académica no puede estar vacío.',
            'comment.min' => 'El comentario debe contener al menos 5 caracteres.',
        ];
    }
}
