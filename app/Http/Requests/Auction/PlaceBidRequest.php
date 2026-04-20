<?php

namespace App\Http\Requests\Auction;

use Illuminate\Foundation\Http\FormRequest;

class PlaceBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fantasy_team_id' => ['required', 'exists:fantasy_teams,id'],
            'player_id' => ['required', 'exists:players,id'],
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'fantasy_team_id.exists' => 'the selected fantasy team does not exist.',
            'player_id.exists' => 'the selected player does not exist.',
            'amount.min' => 'the bid amount must be at least 1.',
        ];
    }
}
