<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Controllers\Web\Concerns\InteractsWithApi;
use App\Services\ApiClient;
use Illuminate\Support\Carbon;

abstract class Controller extends BaseController
{
    use InteractsWithApi;

    protected function apiData(array $response): mixed
    {
        return $response['data'] ?? null;
    }

    protected function currentUser(): ?array
    {
        return session('api_user');
    }

    protected function currentUserId(): ?int
    {
        return data_get($this->currentUser(), 'id');
    }

    protected function fetchCurrentUser(ApiClient $apiClient): array
    {
        $user = $this->apiData($apiClient->me());

        if (is_array($user)) {
            $this->rememberApiUser($user);
        }

        return is_array($user) ? $user : [];
    }

    protected function findMembershipByLeague(array $memberships, int $leagueId): ?array
    {
        foreach ($memberships as $membership) {
            if ((int) data_get($membership, 'fantasy_league_id') === $leagueId) {
                return $membership;
            }
        }

        return null;
    }

    protected function findMembershipByTeam(array $memberships, int $teamId, ?int $membershipId = null): ?array
    {
        foreach ($memberships as $membership) {
            if ((int) data_get($membership, 'id') === $membershipId) {
                return $membership;
            }

            if ((int) data_get($membership, 'fantasy_team.id') === $teamId) {
                return $membership;
            }
        }

        return null;
    }

    protected function resolveWeekFromCompetition(array $competition, int $weekId): ?array
    {
        foreach (data_get($competition, 'weeks', []) as $week) {
            if ((int) data_get($week, 'id') === $weekId) {
                return $week;
            }
        }

        return null;
    }

    protected function resolveCompetitionForWeek(
        ApiClient $apiClient,
        int $weekId,
        ?int $competitionId = null,
    ): ?array {
        if ($competitionId !== null) {
            $competition = $this->apiData($apiClient->competition($competitionId));

            if (is_array($competition) && $this->resolveWeekFromCompetition($competition, $weekId)) {
                return $competition;
            }
        }

        foreach ($this->apiData($apiClient->competitions()) as $entry) {
            $competition = $this->apiData($apiClient->competition((int) data_get($entry, 'id')));

            if (is_array($competition) && $this->resolveWeekFromCompetition($competition, $weekId)) {
                return $competition;
            }
        }

        return null;
    }

    protected function apiDateTime(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value, config('app.timezone'))->utc()->toISOString();
    }

    protected function rememberKnownInvitation(array $invitation): void
    {
        $leagueId = (int) data_get($invitation, 'fantasy_league_id');
        $known = session('known_invitations', []);
        $leagueInvitations = collect($known[$leagueId] ?? [])
            ->reject(fn (array $item) => (int) data_get($item, 'id') === (int) data_get($invitation, 'id'))
            ->push($invitation)
            ->values()
            ->all();

        $known[$leagueId] = $leagueInvitations;

        session(['known_invitations' => $known]);
    }

    protected function forgetKnownInvitation(int $leagueId, int $invitationId): void
    {
        $known = session('known_invitations', []);
        $known[$leagueId] = collect($known[$leagueId] ?? [])
            ->reject(fn (array $invitation) => (int) data_get($invitation, 'id') === $invitationId)
            ->values()
            ->all();

        session(['known_invitations' => $known]);
    }

    protected function knownInvitations(int $leagueId): array
    {
        return collect(session('known_invitations', [])[$leagueId] ?? [])
            ->sortByDesc(fn (array $invitation) => data_get($invitation, 'expires_at'))
            ->values()
            ->all();
    }

    protected function rememberKnownAuction(array $auction): void
    {
        $leagueId = (int) data_get($auction, 'fantasy_league_id');
        $known = session('known_auctions', []);
        $leagueAuctions = collect($known[$leagueId] ?? [])
            ->reject(fn (array $item) => (int) data_get($item, 'id') === (int) data_get($auction, 'id'))
            ->push($auction)
            ->values()
            ->all();

        $known[$leagueId] = $leagueAuctions;

        session(['known_auctions' => $known]);
    }

    protected function knownAuctions(int $leagueId): array
    {
        return collect(session('known_auctions', [])[$leagueId] ?? [])
            ->sortByDesc(fn (array $auction) => data_get($auction, 'start_at'))
            ->values()
            ->all();
    }
}
