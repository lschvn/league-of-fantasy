<?php

use App\Models\FantasyLeague;
use App\Models\Week;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds a reviewable competition and public fantasy league', function () {
    $this->seed();

    $response = $this->getJson('/api/competitions');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.name', 'League of Legends EMEA Championship');

    expect(FantasyLeague::where('name', 'Review League')->exists())->toBeTrue();
});

it('can authenticate with a seeded demo account', function () {
    $this->seed();

    $response = $this->postJson('/api/login', [
        'email' => 'owner@fantasy.test',
        'password' => config('demo.password'),
        'device_name' => 'pest',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'token',
            'user' => ['id', 'name', 'email'],
        ]);
});

it('returns seeded standings for the public review league', function () {
    $this->seed();

    $login = $this->postJson('/api/login', [
        'email' => 'owner@fantasy.test',
        'password' => config('demo.password'),
        'device_name' => 'pest',
    ])->json();

    $league = FantasyLeague::where('name', 'Review League')->firstOrFail();
    $week = Week::where('competition_id', $league->competition_id)->where('number', 1)->firstOrFail();

    $response = $this
        ->withToken($login['token'])
        ->getJson("/api/fantasy-leagues/{$league->id}/weeks/{$week->id}/standings");

    $response
        ->assertOk()
        ->assertJsonCount(4, 'data');
});
