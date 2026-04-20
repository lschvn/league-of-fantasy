<?php

namespace App\Http\Requests\Invitation;

use Illuminate\Foundation\Http\FormRequest;

class JoinPrivateLeagueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper((string) $this->input('code')),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:64'],
            'team_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'an invitation code is required.',
            'team_name.max' => 'the team name may not be longer than 255 characters.',
        ];
    }
}
