<?php

namespace App\Http\Requests\Income;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreIncomeRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        $rules = [
            'amount' => ['required', 'numeric', 'min:0'],
            'income_date' => ['required', 'date', 'before_or_equal:today'],
            'income_type_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Appliquer des règles supplémentaires uniquement si on crée un nouveau type
        if ($this->input('income_type_id') == 0) {
            $rules['new_type_name'] = ['required', 'string', 'between:2,63'];
            $rules['new_type_description'] = ['nullable', 'string', 'max:255'];
            $rules['taxable'] = ['nullable', 'boolean'];
            $rules['must_declare'] = ['nullable', 'boolean'];
        } else {
            // Valider que l'ID existe uniquement s'il n'est pas égal à 0
            $rules['income_type_id'][] = 'exists:income_types,id';
        }

        return $rules;
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
            'notes.max' => 'Les notes ne peuvent pas dépasser 1000 caractères',
            'new_type_name.required_if' => 'Le nom du nouveau type est requis',
            'new_type_name.between' => 'Le nom du type doit faire entre 2 et 63 caractères',
            'new_type_description.max' => 'La description ne peut pas dépasser 255 caractères'
        ];
    }

    protected function failedValidation($validator) {
        Log::error('Validation failed', [
            'errors' => $validator->errors()->toArray(),
            'data' => $this->all()
        ]);

        parent::failedValidation($validator);
    }
}
