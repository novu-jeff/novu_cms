<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommittee extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can implement auth logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('standing_comittee', 'name')->where('isActive', true),
            ],
            'committee' => 'required|array|min:1',
            'committee.*.name' => 'required|string|max:255',
            'committee.*.position' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom error messages (optional).
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The committee name is required.',
            'committee.required' => 'At least one member is required.',
            'committee.*.name.required' => 'Each member must have a name.',
        ];
    }
}
