<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Team;
use App\Models\Week;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PandaScoreService
{
    private const GAME = 'lol';

    private const PER_PAGE = 100;

    public function sync(): array
    {
        return [
            'matches' => $this->syncMatches(),
            'players' => $this->syncPlayers(),
        ];
    }

    public function syncMatches(): int
    {
        return $this->syncCollection(
            '/'.self::GAME.'/matches',
            'matches',
            fn (array $payload): bool => $this->storeMatch($payload)
        );
    }

    public function syncPlayers(): int
    {
        return $this->syncCollection(
            '/'.self::GAME.'/players',
            'players',
            fn (array $payload): bool => $this->storePlayer($payload)
        );
    }

    private function syncCollection(string $endpoint, string $resourceName, callable $store): int
    {
        $page = 1;
        $stored = 0;
        $receivedPayload = false;

        while (true) {
            $items = $this->requestCollection($endpoint, $resourceName, [
                'page' => $page,
                'per_page' => self::PER_PAGE,
            ]);

            if ($items === []) {
                if (! $receivedPayload) {
                    throw new RuntimeException("PandaScore returned an empty {$resourceName} response.");
                }

                break;
            }

            $receivedPayload = true;

            foreach ($items as $item) {
                if (! is_array($item)) {
                    Log::warning("Skipping malformed PandaScore {$resourceName} item.", [
                        'item' => $item,
                    ]);

                    continue;
                }

                if ($store($item)) {
                    $stored++;
                }
            }

            if (count($items) < self::PER_PAGE) {
                break;
            }

            $page++;
        }

        return $stored;
    }

    private function storeMatch(array $payload): bool
    {
        $matchId = $this->extractIdentifier($payload, 'match');
        $matchDate = $this->resolveMatchDate($payload);

        if (! $matchDate) {
            Log::warning('Skipping PandaScore match without a schedule.', [
                'match_id' => $matchId,
            ]);

            return false;
        }

        DB::transaction(function () use ($payload, $matchId, $matchDate): void {
            $competition = $this->syncCompetition($payload, $matchDate);
            $week = $this->syncWeek($competition, $matchDate);
            $match = $this->findGameMatch($matchId, $week, $matchDate);

            $match->fill([
                'pandascore_id' => $matchId,
                'week_id' => $week->id,
                'status' => $this->normalizeMatchStatus((string) ($payload['status'] ?? 'scheduled')),
                'started_at' => $this->resolveStartedAt($payload),
                'ended_at' => $this->resolveEndedAt($payload),
            ]);
            $match->save();

            $teamSyncData = [];

            foreach ($payload['opponents'] ?? [] as $opponentPayload) {
                $team = $this->syncTeam($competition, $opponentPayload);

                if (! $team) {
                    continue;
                }

                $teamSyncData[$team->id] = [
                    'side' => data_get($opponentPayload, 'type'),
                ];
            }

            if ($teamSyncData !== []) {
                $match->teams()->sync($teamSyncData);
            }
        });

        return true;
    }

    private function storePlayer(array $payload): bool
    {
        $playerId = $this->extractIdentifier($payload, 'player');
        $teamId = data_get($payload, 'current_team.id');

        if (! is_numeric($teamId)) {
            Log::warning('Skipping PandaScore player without a current team.', [
                'player_id' => $playerId,
            ]);

            return false;
        }

        $team = Team::query()->where('pandascore_id', (int) $teamId)->first();

        if (! $team) {
            Log::warning('Skipping PandaScore player because the team is not synchronized yet.', [
                'player_id' => $playerId,
                'team_id' => (int) $teamId,
            ]);

            return false;
        }

        $nickname = (string) ($payload['name'] ?? $payload['slug'] ?? "player-{$playerId}");
        $player = Player::query()->where('pandascore_id', $playerId)->first();

        if (! $player) {
            $player = Player::query()
                ->where('team_id', $team->id)
                ->where('nickname', $nickname)
                ->first() ?? new Player();
        }

        $player->fill([
            'pandascore_id' => $playerId,
            'team_id' => $team->id,
            'nickname' => Str::limit($nickname, 255, ''),
            'role' => $this->normalizeRole((string) ($payload['role'] ?? '')),
            'status' => ($payload['active'] ?? true) ? 'active' : 'inactive',
        ]);
        $player->save();

        return true;
    }

    private function requestCollection(string $endpoint, string $resourceName, array $query = []): array
    {
        $response = $this->sendRequest($endpoint, $resourceName, $query);
        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException("PandaScore {$resourceName} response is invalid.");
        }

        return $payload;
    }

    private function sendRequest(string $endpoint, string $resourceName, array $query = []): Response
    {
        $apiKey = (string) config('services.pandascore.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('PandaScore API key is not configured.');
        }

        try {
            $response = Http::baseUrl(rtrim((string) config('services.pandascore.base_url'), '/'))
                ->acceptJson()
                ->withToken($apiKey)
                ->timeout(30)
                ->get(ltrim($endpoint, '/'), $query);
        } catch (ConnectionException $exception) {
            Log::error("Unable to reach PandaScore {$resourceName} endpoint.", [
                'endpoint' => $endpoint,
                'query' => $query,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Unable to reach PandaScore API.', previous: $exception);
        }

        if ($response->status() === 429) {
            $retryAfter = $response->header('Retry-After');
            $message = 'PandaScore rate limit reached.';

            if ($retryAfter !== null) {
                $message .= " Retry after {$retryAfter} seconds.";
            }

            Log::warning($message, [
                'endpoint' => $endpoint,
                'query' => $query,
            ]);

            throw new RuntimeException($message);
        }

        if ($response->failed()) {
            Log::error("PandaScore {$resourceName} request failed.", [
                'endpoint' => $endpoint,
                'query' => $query,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException(
                "PandaScore {$resourceName} request failed with status {$response->status()}."
            );
        }

        $remainingRequests = $response->header('X-Rate-Limit-Remaining');

        if ($remainingRequests !== null && is_numeric($remainingRequests) && (int) $remainingRequests <= 5) {
            Log::warning('PandaScore rate limit is running low.', [
                'remaining' => (int) $remainingRequests,
                'resource' => $resourceName,
            ]);
        }

        return $response;
    }

    private function syncCompetition(array $payload, Carbon $matchDate): Competition
    {
        $competitionId = data_get($payload, 'league.id');

        if (! is_numeric($competitionId)) {
            throw new RuntimeException('PandaScore match payload is missing a valid league id.');
        }

        $name = (string) (data_get($payload, 'league.name') ?? 'unknown competition');
        $season = (string) $matchDate->year;
        $competition = Competition::query()->where('pandascore_id', (int) $competitionId)->first();

        if (! $competition) {
            $competition = Competition::query()
                ->where('name', $name)
                ->where('season', $season)
                ->first();
        }

        $competition ??= new Competition();
        $competition->fill([
            'pandascore_id' => (int) $competitionId,
            'name' => Str::limit($name, 255, ''),
            'region' => Str::limit((string) (data_get($payload, 'league.region') ?? 'global'), 50, ''),
            'season' => Str::limit($season, 50, ''),
        ]);
        $competition->save();

        return $competition;
    }

    private function syncWeek(Competition $competition, Carbon $matchDate): Week
    {
        return Week::query()->updateOrCreate(
            [
                'competition_id' => $competition->id,
                'number' => $matchDate->isoWeek(),
            ],
            [
                'start_at' => $matchDate->copy()->startOfWeek(),
                'end_at' => $matchDate->copy()->endOfWeek(),
                'lineup_lock_at' => $matchDate->copy()->startOfWeek(),
            ]
        );
    }

    private function syncTeam(Competition $competition, array $payload): ?Team
    {
        $opponent = data_get($payload, 'opponent');

        if (! is_array($opponent)) {
            return null;
        }

        $teamId = data_get($opponent, 'id');

        if (! is_numeric($teamId)) {
            Log::warning('Skipping PandaScore opponent without a valid id.', [
                'opponent' => $opponent,
            ]);

            return null;
        }

        $name = (string) (data_get($opponent, 'name') ?? data_get($opponent, 'slug') ?? "team-{$teamId}");
        $tag = $this->normalizeTag((string) (data_get($opponent, 'acronym') ?? $name), (int) $teamId);
        $team = Team::query()->where('pandascore_id', (int) $teamId)->first();

        if (! $team) {
            $team = Team::query()
                ->where('competition_id', $competition->id)
                ->where(function ($query) use ($name, $tag): void {
                    $query->where('tag', $tag)->orWhere('name', $name);
                })
                ->first();
        }

        $team ??= new Team();
        $team->fill([
            'pandascore_id' => (int) $teamId,
            'competition_id' => $competition->id,
            'name' => Str::limit($name, 255, ''),
            'tag' => $tag,
            'logo_url' => data_get($opponent, 'image_url'),
        ]);
        $team->save();

        return $team;
    }

    private function findGameMatch(int $matchId, Week $week, Carbon $matchDate): GameMatch
    {
        return GameMatch::query()->where('pandascore_id', $matchId)->first()
            ?? GameMatch::query()
                ->where('week_id', $week->id)
                ->where('started_at', $matchDate)
                ->first()
            ?? new GameMatch();
    }

    private function resolveMatchDate(array $payload): ?Carbon
    {
        foreach (['begin_at', 'scheduled_at', 'original_scheduled_at'] as $field) {
            $value = $payload[$field] ?? null;

            if (is_string($value) && $value !== '') {
                return Carbon::parse($value);
            }
        }

        return null;
    }

    private function resolveEndedAt(array $payload): ?Carbon
    {
        $value = $payload['end_at'] ?? null;

        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function resolveStartedAt(array $payload): ?Carbon
    {
        $status = (string) ($payload['status'] ?? '');

        if (in_array($status, ['not_started', 'postponed', 'canceled'], true)) {
            return null;
        }

        return $this->resolveMatchDate($payload);
    }

    private function normalizeMatchStatus(string $status): string
    {
        return match ($status) {
            'not_started' => 'scheduled',
            default => Str::limit(Str::lower($status), 20, ''),
        };
    }

    private function normalizeRole(string $role): string
    {
        return match (Str::upper($role)) {
            'TOP' => 'TOP',
            'JUNGLE', 'JGL', 'JUNGLER' => 'JGL',
            'MID', 'MIDDLE' => 'MID',
            'ADC', 'BOT', 'BOTTOM' => 'ADC',
            'SUP', 'SUPPORT' => 'SUP',
            default => Str::limit(Str::upper($role !== '' ? $role : 'UNK'), 10, ''),
        };
    }

    private function normalizeTag(string $value, int $teamId): string
    {
        $normalized = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');

        if ($normalized === '') {
            $normalized = 'TEAM'.$teamId;
        }

        return Str::limit($normalized, 10, '');
    }

    private function extractIdentifier(array $payload, string $resourceName): int
    {
        $identifier = $payload['id'] ?? null;

        if (! is_numeric($identifier)) {
            throw new RuntimeException("PandaScore {$resourceName} payload is missing a valid id.");
        }

        return (int) $identifier;
    }
}
