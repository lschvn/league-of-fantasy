<?php

namespace App\Http\Requests\Invitation;

use Illuminate\Foundation\Http\FormRequest;

class JoinPrivateLeagueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:64'],
            'team_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
