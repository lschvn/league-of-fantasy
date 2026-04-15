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
}
