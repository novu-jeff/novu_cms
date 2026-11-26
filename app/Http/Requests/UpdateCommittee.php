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
     * Custom error messages (optional)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The committee name is required.',
            'name.unique' => 'This committee name is already taken.',
            'committee.required' => 'At least one member is required.',
            'committee.*.member_id.required' => 'Please select an SB member.',
            'committee.*.member_id.distinct' => 'An SB member cannot be assigned more than once to the same committee.',
            'committee.*.member_id.exists' => 'The selected SB member is invalid or inactive.',
            'committee.*.position.required' => 'Please select a committee role.',
            'committee.*.position.in' => 'The selected committee role is invalid.',
        ];
    }
}
