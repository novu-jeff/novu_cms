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
                Rule::unique('standing_comittee', 'name')
                    ->where('isActive', true),
            ],

            'committee' => [
                'required',
                'array',
                'min:1',
            ],

            'committee.*.member_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('members', 'id')
                    ->where('isActive', true),
            ],

            'committee.*.position' => [
                'required',
                Rule::in([
                    'Chairperson',
                    'Vice-Chairperson',
                    'Member',
                ]),
            ],
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
            'committee.*.member_id.required' => 'Please select an SB member.',
            'committee.*.member_id.distinct' => 'An SB member cannot be assigned more than once to the same committee.',
            'committee.*.member_id.exists' => 'The selected SB member is invalid or inactive.',
            'committee.*.position.required' => 'Please select a committee role.',
            'committee.*.position.in' => 'The selected committee role is invalid.',
        ];
    }
}
