<?php

namespace App\Http\Requests\FantasyLeague;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFantasyLeagueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'competition_id' => ['required', 'exists:competitions,id'],
            'name' => ['required', 'string', 'max:255'],
            'visibility' => ['required', Rule::in(['public', 'private'])],
            'status' => ['nullable', Rule::in(['draft', 'open', 'locked', 'closed'])],
            'max_participants' => ['required', 'integer', 'min:2', 'max:64'],
            'budget_cap' => ['required', 'numeric', 'min:1'],
            'join_deadline' => ['required', 'date', 'after:now'],
            'scoring_rule_version' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'competition_id.exists' => 'the selected competition does not exist.',
            'join_deadline.after' => 'the join deadline must be in the future.',
        ];
    }
}
