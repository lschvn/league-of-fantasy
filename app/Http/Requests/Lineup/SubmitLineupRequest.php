<?php

namespace App\Http\Requests\Lineup;

use App\Support\FantasyLineup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitLineupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week_id' => ['required', 'exists:weeks,id'],
            'slots' => ['required', 'array', 'size:7'],
            'slots.*.roster_slot_id' => ['required', 'exists:roster_slots,id'],
            'slots.*.position' => ['required', Rule::in(FantasyLineup::REQUIRED_POSITIONS)],
            'slots.*.is_captain' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slots.size' => 'a lineup must contain exactly 7 slots.',
            'slots.*.position.in' => 'each lineup position must match the allowed fantasy lineup positions.',
        ];
    }
}
