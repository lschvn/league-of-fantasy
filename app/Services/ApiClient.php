<?php

namespace App\Services;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiClient
{
    public function register(array $payload): array
    {
        return $this->send('post', '/register', $payload, false);
    }

    public function login(array $payload): array
    {
        return $this->send('post', '/login', $payload, false);
    }

    public function logout(): array
    {
        return $this->send('post', '/logout');
    }

    public function me(): array
    {
        return $this->send('get', '/me');
    }

    public function competitions(): array
    {
        return $this->send('get', '/competitions', [], false);
    }

    public function competition(int $competitionId): array
    {
        return $this->send('get', "/competitions/{$competitionId}", [], false);
    }

    public function competitionWeeks(int $competitionId): array
    {
        return $this->send('get', "/competitions/{$competitionId}/weeks", [], false);
    }

    public function weekMatches(int $weekId): array
    {
        return $this->send('get', "/weeks/{$weekId}/matches", [], false);
    }

    public function match(int $matchId): array
    {
        return $this->send('get', "/matches/{$matchId}", [], false);
    }

    public function matchPlayerStats(int $matchId): array
    {
        return $this->send('get', "/matches/{$matchId}/player-stats", [], false);
    }

    public function fantasyLeagues(array $query = []): array
    {
        return $this->send('get', '/fantasy-leagues', $query, false);
    }

    public function fantasyLeague(int $leagueId): array
    {
        return $this->send('get', "/fantasy-leagues/{$leagueId}");
    }

    public function fantasyLeagueAuctions(int $leagueId): array
    {
        return $this->send('get', "/fantasy-leagues/{$leagueId}/auctions");
    }

    public function createFantasyLeague(array $payload): array
    {
        return $this->send('post', '/fantasy-leagues', $payload);
    }

    public function joinFantasyLeague(int $leagueId, array $payload = []): array
    {
        return $this->send('post', "/fantasy-leagues/{$leagueId}/join", $payload);
    }

    public function fantasyLeagueMembers(int $leagueId): array
    {
        return $this->send('get', "/fantasy-leagues/{$leagueId}/members");
    }

    public function fantasyLeagueStandings(int $leagueId, int $weekId): array
    {
        return $this->send('get', "/fantasy-leagues/{$leagueId}/weeks/{$weekId}/standings");
    }

    public function createInvitation(int $leagueId, array $payload): array
    {
        return $this->send('post', "/fantasy-leagues/{$leagueId}/invitations", $payload);
    }

    public function joinPrivateLeague(array $payload): array
    {
        return $this->send('post', '/private-leagues/join', $payload);
    }

    public function revokeInvitation(int $invitationId): array
    {
        return $this->send('delete', "/invitations/{$invitationId}");
    }

    public function fantasyTeam(int $teamId): array
    {
        return $this->send('get', "/fantasy-teams/{$teamId}");
    }

    public function fantasyTeamRoster(int $teamId): array
    {
        return $this->send('get', "/fantasy-teams/{$teamId}/roster");
    }

    public function releaseRosterSlot(int $teamId, int $rosterSlotId): array
    {
        return $this->send('delete', "/fantasy-teams/{$teamId}/roster/{$rosterSlotId}");
    }

    public function fantasyTeamLineup(int $teamId, int $weekId): array
    {
        return $this->send('get', "/fantasy-teams/{$teamId}/weeks/{$weekId}/lineup");
    }

    public function submitLineup(int $teamId, array $payload): array
    {
        return $this->send('post', "/fantasy-teams/{$teamId}/lineups", $payload);
    }

    public function auction(int $auctionId): array
    {
        return $this->send('get', "/auctions/{$auctionId}");
    }

    public function auctionBids(int $auctionId): array
    {
        return $this->send('get', "/auctions/{$auctionId}/bids");
    }

    public function auctionPlayers(int $auctionId): array
    {
        return $this->send('get', "/auctions/{$auctionId}/players");
    }

    public function placeBid(int $auctionId, array $payload): array
    {
        return $this->send('post', "/auctions/{$auctionId}/bids", $payload);
    }

    public function closeAuction(int $auctionId): array
    {
        return $this->send('post', "/auctions/{$auctionId}/close");
    }

    protected function send(string $method, string $uri, array $payload = [], bool $authenticated = true): array
    {
        if ($this->shouldDispatchInternally()) {
            return $this->dispatchInternally($method, $uri, $payload, $authenticated);
        }

        $client = $this->client($authenticated);

        $response = match (strtolower($method)) {
            'delete' => $client->delete($uri, $payload),
            'get' => $client->get($uri, $payload),
            'post' => $client->post($uri, $payload),
            default => throw new ApiException(500, "Unsupported API method [{$method}]."),
        };

        return $this->handleResponse($response);
    }

    protected function client(bool $authenticated = true): PendingRequest
    {
        $client = Http::baseUrl($this->apiBaseUrl())
            ->acceptJson()
            ->asJson();

        if ($authenticated && session('api_token')) {
            $client = $client->withToken(session('api_token'));
        }

        return $client;
    }

    protected function apiBaseUrl(): string
    {
        return rtrim((string) config('services.app_api.base_url', config('app.url')), '/').'/api';
    }

    protected function shouldDispatchInternally(): bool
    {
        return blank(config('services.app_api.base_url'));
    }

    protected function dispatchInternally(
        string $method,
        string $uri,
        array $payload = [],
        bool $authenticated = true,
    ): array {
        $originalRequest = app()->bound('request') ? app('request') : null;
        $request = Request::create(
            '/api/'.ltrim($uri, '/'),
            strtoupper($method),
            strtolower($method) === 'get' ? $payload : [],
            $originalRequest?->cookies->all() ?? [],
            [],
            $this->serverVariables($originalRequest, $authenticated, $method),
            $this->requestContent($method, $payload),
        );

        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        if ($authenticated && session()->has('api_token')) {
            $request->headers->set('Authorization', 'Bearer '.session('api_token'));
        }

        if ($this->methodHasJsonBody($method)) {
            $request->headers->set('Content-Type', 'application/json');
        }

        try {
            $response = app(Kernel::class)->handle($request);
        } finally {
            if ($originalRequest !== null) {
                app()->instance('request', $originalRequest);
            }
        }

        return $this->handleDecodedResponse(
            $response->getStatusCode(),
            $this->decodeResponseContent($response->getContent()),
        );
    }

    protected function serverVariables(?Request $originalRequest, bool $authenticated, string $method): array
    {
        return array_filter([
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_HOST' => $originalRequest?->getHttpHost(),
            'REQUEST_METHOD' => strtoupper($method),
            'SERVER_PORT' => $originalRequest?->getPort(),
            'HTTPS' => $originalRequest?->isSecure() ? 'on' : null,
            'HTTP_AUTHORIZATION' => $authenticated && session()->has('api_token')
                ? 'Bearer '.session('api_token')
                : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    protected function requestContent(string $method, array $payload): ?string
    {
        if (! $this->methodHasJsonBody($method) || $payload === []) {
            return null;
        }

        return json_encode($payload);
    }

    protected function methodHasJsonBody(string $method): bool
    {
        return in_array(strtolower($method), ['delete', 'post'], true);
    }

    protected function decodeResponseContent(string $content): mixed
    {
        if ($content === '') {
            return [];
        }

        $decoded = json_decode($content, true);

        return json_last_error() === JSON_ERROR_NONE
            ? $decoded
            : ['message' => $content];
    }

    protected function handleResponse(Response $response): array
    {
        return $this->handleDecodedResponse($response->status(), $response->json());
    }

    protected function handleDecodedResponse(int $status, mixed $body): array
    {
        if ($status >= 200 && $status < 300) {
            return is_array($body) ? $body : [];
        }

        throw new ApiException(
            $status,
            is_array($body) ? ($body['message'] ?? 'API request failed.') : 'API request failed.',
            is_array($body['errors'] ?? null) ? $body['errors'] : [],
            $body
        );
    }
}
