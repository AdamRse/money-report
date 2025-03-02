<?php

namespace App\Http\Requests\IncomeImport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ImportIncomesRequest extends FormRequest {
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
            'incomes' => ['required', 'array'],
            'incomes.*.date' => ['required', 'string'],
            'incomes.*.description' => ['required', 'string'],
            'incomes.*.amount' => ['required', 'numeric', 'min:0'],
            'incomes.*.income_type_id' => [
                'exclude_unless:incomes.*.selected,on',
                'required',
                'exists:income_types,id'
            ],
            'incomes.*.selected' => ['sometimes']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'incomes.required' => 'Aucun revenu n\'a été fourni',
            'incomes.array' => 'Format de données invalide',
            'incomes.*.date.required' => 'La date est requise pour tous les revenus',
            'incomes.*.description.required' => 'La description est requise pour tous les revenus',
            'incomes.*.amount.required' => 'Le amount est requis pour tous les revenus',
            'incomes.*.amount.numeric' => 'Le amount doit être un nombre',
            'incomes.*.amount.min' => 'Le amount doit être positif',
            'incomes.*.income_type_id.required' => 'Le type de revenu est requis pour tous les revenus',
            'incomes.*.income_type_id.exists' => 'Le type de revenu sélectionné n\'existe pas'
        ];
    }
}
