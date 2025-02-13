<?php
// app/Http/Requests/Income/UpdateIncomeRequest.php

namespace App\Http\Requests\Income;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'income_date' => ['required', 'date', 'before_or_equal:today'],
            'income_type_id' => ['required', 'exists:income_types,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array {
        return [
            'amount.required' => 'Le amount est requis',
            'amount.numeric' => 'Le amount doit être un nombre',
            'amount.min' => 'Le amount doit être positif',
            'income_date.required' => 'La date est requise',
            'income_date.date' => 'La date doit être valide',
            'income_date.before_or_equal' => 'La date ne peut pas être dans le futur',
            'income_type_id.required' => 'Le type de revenu est requis',
            'income_type_id.exists' => 'Le type de revenu sélectionné n\'existe pas',
            'notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères'
        ];
    }
}
