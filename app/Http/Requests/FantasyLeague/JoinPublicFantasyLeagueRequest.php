<?php

namespace App\Http\Requests\FantasyLeague;

use Illuminate\Foundation\Http\FormRequest;

class JoinPublicFantasyLeagueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'team_name.max' => 'the team name may not be longer than 255 characters.',
        ];
    }
}
