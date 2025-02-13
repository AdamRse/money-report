<?php

namespace App\Http\Requests\IncomeReport;

use Illuminate\Foundation\Http\FormRequest;

class FilterIncomesRequest extends FormRequest {
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
        $rules = [
            'filter_type' => ['nullable', 'in:period,month'],
        ];

        if ($this->filter_type === 'period') {
            $rules['start_date'] = ['required', 'date', 'before_or_equal:today'];
            $rules['end_date'] = [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:start_date'
            ];
        } elseif ($this->filter_type === 'month') {
            $rules['month_number'] = ['required', 'numeric', 'between:1,12'];
            $rules['year_number'] = [
                'required',
                'numeric',
                'min:1900',
                'max:' . date('Y')
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'start_date.required' => 'La date de début est requise',
            'end_date.required' => 'La date de fin est requise',
            'start_date.before_or_equal' => 'La date de début ne peut pas être dans le futur',
            'end_date.before_or_equal' => 'La date de fin ne peut pas être dans le futur',
            'end_date.after_or_equal' => 'La date de fin doit être après la date de début',
            'month_number.required' => 'Le mois est requis',
            'month_number.between' => 'Le mois doit être compris entre 1 et 12',
            'year_number.required' => 'L\'année est requise',
            'year_number.max' => 'L\'année ne peut pas être dans le futur'
        ];
    }
}
