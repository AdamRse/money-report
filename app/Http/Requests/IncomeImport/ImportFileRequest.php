<?php

namespace App\Http\Requests\IncomeImport;

use Illuminate\Foundation\Http\FormRequest;

class ImportFileRequest extends FormRequest {
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
            'bankFile' => ['required', 'file', 'mimes:csv,tsv,txt', 'max:2048']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'bankFile.required' => 'Un fichier est requis',
            'bankFile.file' => 'Le fichier est invalide',
            'bankFile.mimes' => 'Le fichier doit être au format CSV ou TSV',
            'bankFile.max' => 'Le fichier ne doit pas dépasser 2Mo'
        ];
    }
}
