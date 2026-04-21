<?php

use App\Models\Auction;
use App\Models\Competition;
use App\Models\FantasyLeague;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Models\Week;
use App\Services\LeagueMembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists fantasy league auctions with week data for authorized users', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $competition = Competition::factory()->create();
    $weekOne = Week::factory()->for($competition)->create(['number' => 1]);
    $weekTwo = Week::factory()->for($competition)->create(['number' => 2]);

    $league = FantasyLeague::factory()
        ->private()
        ->for($competition)
        ->for($owner, 'creator')
        ->create();

    $membershipService = app(LeagueMembershipService::class);
    $membershipService->addOwner($league, $owner);
    $membershipService->join($league, $member, 'member team');

    Auction::factory()->for($league)->for($weekOne)->create(['start_at' => now()->subDays(2)]);
    Auction::factory()->for($league)->for($weekTwo)->create(['start_at' => now()->subDay()]);

    $token = $member->createToken('pest')->plainTextToken;

    $response = $this
        ->withToken($token)
        ->getJson("/api/fantasy-leagues/{$league->id}/auctions");

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                ['id', 'week' => ['id', 'number']],
            ],
        ]);
});

it('forbids non-members from listing private league auctions', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $competition = Competition::factory()->create();
    $week = Week::factory()->for($competition)->create();

    $league = FantasyLeague::factory()
        ->private()
        ->for($competition)
        ->for($owner, 'creator')
        ->create();

    app(LeagueMembershipService::class)->addOwner($league, $owner);
    Auction::factory()->for($league)->for($week)->create();

    $token = $outsider->createToken('pest')->plainTextToken;

    $this->withToken($token)
        ->getJson("/api/fantasy-leagues/{$league->id}/auctions")
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'forbidden.');
});

it('lists active auction players in the auction competition with team data', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $competition = Competition::factory()->create();
    $otherCompetition = Competition::factory()->create();
    $week = Week::factory()->for($competition)->create();

    $league = FantasyLeague::factory()
        ->private()
        ->for($competition)
        ->for($owner, 'creator')
        ->create();

    $membershipService = app(LeagueMembershipService::class);
    $membershipService->addOwner($league, $owner);
    $membershipService->join($league, $member, 'member team');

    $auction = Auction::factory()->for($league)->for($week)->create();

    $competitionTeam = Team::factory()->for($competition)->create();
    $otherCompetitionTeam = Team::factory()->for($otherCompetition)->create();

    $eligiblePlayer = Player::factory()->for($competitionTeam)->create([
        'nickname' => 'eligible_player',
        'status' => 'active',
    ]);
    Player::factory()->for($competitionTeam)->create([
        'nickname' => 'inactive_player',
        'status' => 'inactive',
    ]);
    Player::factory()->for($otherCompetitionTeam)->create([
        'nickname' => 'other_competition_player',
        'status' => 'active',
    ]);

    $token = $member->createToken('pest')->plainTextToken;

    $response = $this
        ->withToken($token)
        ->getJson("/api/auctions/{$auction->id}/players");

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                ['id', 'nickname', 'team_id', 'team' => ['id', 'name', 'tag']],
            ],
        ]);

    $nicknames = collect($response->json('data'))->pluck('nickname');
    $eligible = collect($response->json('data'))->firstWhere('id', $eligiblePlayer->id);

    expect($nicknames)->toContain('eligible_player');
    expect($nicknames)->not->toContain('inactive_player');
    expect($nicknames)->not->toContain('other_competition_player');
    expect($eligible['team']['id'] ?? null)->toBe($competitionTeam->id);
});

it('forbids non-members from listing auction players', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $competition = Competition::factory()->create();
    $week = Week::factory()->for($competition)->create();

    $league = FantasyLeague::factory()
        ->private()
        ->for($competition)
        ->for($owner, 'creator')
        ->create();

    app(LeagueMembershipService::class)->addOwner($league, $owner);
    $auction = Auction::factory()->for($league)->for($week)->create();

    $token = $outsider->createToken('pest')->plainTextToken;

    $this->withToken($token)
        ->getJson("/api/auctions/{$auction->id}/players")
        ->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'forbidden.');
});
