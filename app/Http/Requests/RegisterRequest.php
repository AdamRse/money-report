<?php
// app/Http/Requests/RegisterRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<string>>
     */
    public function rules(): array {
        return [
            'user' => ['required', 'string', 'max:31'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'user.required' => 'Le nom d\'utilisateur est requis',
            'user.max' => 'Le nom d\'utilisateur ne peut pas dépasser 31 caractères',
            'email.required' => 'L\'adresse email est requise',
            'email.email' => 'L\'adresse email doit être valide',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères',
            'email.unique' => 'Cette adresse email est déjà utilisée',
            'password.required' => 'Le mot de passe est requis',
            'password.min' => 'Le mot de passe doit faire au moins 4 caractères',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas',
        ];
    }
}
