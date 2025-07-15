<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommittee extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('standing_comittee', 'name')
                    ->ignore($this->route('standing_committee'))
                    ->where('isActive', true),
            ],
            'committee' => 'required|array|min:1',
            'committee.*.name' => 'required|string|max:255',
            'committee.*.position' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom error messages (optional)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The committee name is required.',
            'name.unique' => 'This committee name is already taken.',
            'committee.required' => 'At least one member is required.',
            'committee.*.name.required' => 'Each member must have a name.',
        ];
    }
}
