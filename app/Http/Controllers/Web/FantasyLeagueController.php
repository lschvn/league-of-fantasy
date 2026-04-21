<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FantasyLeagueController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function index(): View
    {
        try {
            $leagues = $this->apiData($this->apiClient->fantasyLeagues());
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        return view('pages.leagues.index', [
            'leagues' => $leagues,
        ]);
    }

    public function create(): View
    {
        try {
            $competitions = $this->apiData($this->apiClient->competitions());
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        return view('pages.leagues.create', [
            'competitions' => $competitions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $response = $this->apiClient->createFantasyLeague([
                'competition_id' => (int) $request->input('competition_id'),
                'name' => $request->string('name')->toString(),
                'visibility' => $request->string('visibility')->toString(),
                'max_participants' => (int) $request->input('max_participants'),
                'budget_cap' => (float) $request->input('budget_cap'),
                'join_deadline' => $this->apiDateTime($request->input('join_deadline')),
                'scoring_rule_version' => $request->filled('scoring_rule_version')
                    ? $request->string('scoring_rule_version')->toString()
                    : null,
            ]);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return $this->redirectBackWithApiErrors($request, $exception);
            }

            $this->handleApiException($exception);
        }

        return redirect()
            ->route('leagues.show', ['fantasyLeague' => data_get($response, 'data.id')])
            ->with('success', 'League created successfully.');
    }

    public function show(Request $request, string $fantasyLeague): View
    {
        $leagueId = (int) $fantasyLeague;

        try {
            $currentUserId = $this->currentUserId();

            if ($currentUserId === null) {
                $currentUserId = (int) data_get($this->fetchCurrentUser($this->apiClient), 'id');
            }

            $league = $this->apiData($this->apiClient->fantasyLeague($leagueId));
            $members = $this->apiData($this->apiClient->fantasyLeagueMembers($leagueId));
            $weeks = $this->apiData($this->apiClient->competitionWeeks((int) data_get($league, 'competition_id')));
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        $currentMembership = collect($members)
            ->first(fn (array $membership) => (int) data_get($membership, 'user_id') === $currentUserId);

        $tab = $request->string('tab')->toString();
        $activeTab = in_array($tab, ['overview', 'members', 'standings', 'auctions', 'matches'], true) ? $tab : 'overview';
        $selectedWeekId = (int) ($request->input('week') ?: data_get($weeks, '0.id'));
        $standings = [];
        $standingsError = null;
        $matches = [];
        $matchesError = null;
        $auctions = [];
        $auctionsError = null;

        if ($activeTab === 'standings' && $selectedWeekId > 0) {
            try {
                $standings = $this->apiData($this->apiClient->fantasyLeagueStandings($leagueId, $selectedWeekId));
                $standings = is_array($standings) ? $standings : [];
            } catch (ApiException $exception) {
                if ($exception->status === 422) {
                    $standingsError = $exception->getMessage();
                    $standings = [];
                } else {
                    $this->handleApiException($exception);
                }
            }
        }

        if ($activeTab === 'matches' && $selectedWeekId > 0) {
            try {
                $matches = $this->apiData($this->apiClient->weekMatches($selectedWeekId));
                $matches = is_array($matches) ? $matches : [];
            } catch (ApiException $exception) {
                if ($exception->status === 422) {
                    $matchesError = $exception->getMessage();
                    $matches = [];
                } else {
                    $this->handleApiException($exception);
                }
            }
        }

        if ($activeTab === 'auctions') {
            try {
                $auctions = $this->apiData($this->apiClient->fantasyLeagueAuctions($leagueId));
                $auctions = is_array($auctions) ? $auctions : [];
            } catch (ApiException $exception) {
                if ($exception->status === 422) {
                    $auctionsError = $exception->getMessage();
                    $auctions = [];
                } else {
                    $this->handleApiException($exception);
                }
            }
        }

        $isOwner = in_array((string) data_get($currentMembership, 'role'), ['owner', 'manager'], true);

        // The API does not expose invitation listing, so the web layer can only render invitations
        // created during the current browser session.
        $knownInvitations = $isOwner ? $this->knownInvitations($leagueId) : [];

        return view('pages.leagues.show', [
            'activeTab' => $activeTab,
            'auctions' => $auctions,
            'auctionsError' => $auctionsError,
            'currentMembership' => $currentMembership,
            'isOwner' => $isOwner,
            'league' => $league,
            'members' => $members,
            'matches' => $matches,
            'matchesError' => $matchesError,
            'selectedWeekId' => $selectedWeekId,
            'standings' => $standings,
            'standingsError' => $standingsError,
            'weeks' => $weeks,
            'knownInvitations' => $knownInvitations,
        ]);
    }

    public function join(Request $request, string $fantasyLeague): RedirectResponse
    {
        $leagueId = (int) $fantasyLeague;

        try {
            $this->apiClient->joinFantasyLeague($leagueId, []);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return redirect()
                    ->route('leagues.show', ['fantasyLeague' => $leagueId])
                    ->with('error', $exception->getMessage());
            }

            $this->handleApiException($exception);
        }

        return redirect()
            ->route('leagues.show', ['fantasyLeague' => $leagueId])
            ->with('success', 'League joined successfully.');
    }

    public function joinPrivateForm(): View
    {
        return view('pages.leagues.join-private');
    }

    public function joinPrivate(Request $request): RedirectResponse
    {
        try {
            $response = $this->apiClient->joinPrivateLeague([
                'code' => $request->string('code')->toString(),
                'team_name' => $request->filled('team_name')
                    ? $request->string('team_name')->toString()
                    : null,
            ]);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return $this->redirectBackWithApiErrors($request, $exception);
            }

            $this->handleApiException($exception);
        }

        return redirect()
            ->route('leagues.show', ['fantasyLeague' => data_get($response, 'data.fantasy_league_id')])
            ->with('success', 'Private league joined successfully.');
    }
}
