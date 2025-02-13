<?php

namespace App\Http\Requests\IncomeType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncomeTypeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:63',
                Rule::unique('income_types')->ignore($this->route('id')),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'taxable' => ['sometimes', 'boolean'],
            'must_declare' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'name.required' => 'Le nom du type de revenu est obligatoire',
            'name.min' => 'Le nom doit faire au moins 2 caractères',
            'name.max' => 'Le nom ne peut pas dépasser 63 caractères',
            'name.unique' => 'Ce type de revenu existe déjà',
            'description.max' => 'La description ne peut pas dépasser 255 caractères',
        ];
    }
}
