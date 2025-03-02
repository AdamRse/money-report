<?php
// app/Http/Requests/Auth/LoginRequest.php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest {
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
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['sometimes', 'accepted'] //Boolean ne fonctionnera pas
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'email.required' => 'L\'adresse email est requise',
            'email.email' => 'L\'adresse email doit être valide',
            'password.required' => 'Le mot de passe est requis',
        ];
    }
}
