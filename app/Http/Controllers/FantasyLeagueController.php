<?php

namespace App\Http\Controllers;

use App\Http\Requests\FantasyLeague\JoinPublicFantasyLeagueRequest;
use App\Http\Requests\FantasyLeague\StoreFantasyLeagueRequest;
use App\Http\Resources\FantasyLeagueResource;
use App\Http\Resources\FantasyTeamScoreResource;
use App\Http\Resources\MembershipResource;
use App\Models\FantasyLeague;
use App\Models\Week;
use App\Services\LeagueMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FantasyLeagueController extends Controller
{
    public function __construct(
        private readonly LeagueMembershipService $membershipService
    ) {}

    /**
     * List fantasy leagues available for review.
     *
     * @unauthenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = FantasyLeague::query()
            ->with('competition')
            ->withCount('memberships')
            ->latest();

        if ($request->boolean('public_only', true)) {
            $query->where('visibility', 'public');
        }

        return FantasyLeagueResource::collection($query->get());
    }

    public function store(StoreFantasyLeagueRequest $request): JsonResponse
    {
        $data = $request->validated();

        $league = DB::transaction(function () use ($request, $data) {
            $league = FantasyLeague::create([
                'competition_id' => $data['competition_id'],
                'creator_user_id' => $request->user()->id,
                'name' => $data['name'],
                'visibility' => $data['visibility'],
                'status' => $data['status'] ?? 'open',
                'max_participants' => $data['max_participants'],
                'budget_cap' => $data['budget_cap'],
                'join_deadline' => $data['join_deadline'],
                'scoring_rule_version' => $data['scoring_rule_version'] ?? 'v1',
            ]);

            $this->membershipService->addOwner($league, $request->user());

            return $league;
        });

        return response()->json([
            'message' => 'Fantasy league created successfully.',
            'data' => new FantasyLeagueResource($league->load('competition')),
        ], 201);
    }

    public function show(Request $request, FantasyLeague $fantasyLeague): FantasyLeagueResource|JsonResponse
    {
        if (! $this->isMember($request, $fantasyLeague)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return new FantasyLeagueResource(
            $fantasyLeague->load('competition')->loadCount('memberships')
        );
    }

    public function join(JoinPublicFantasyLeagueRequest $request, FantasyLeague $fantasyLeague): JsonResponse
    {
        if ($fantasyLeague->visibility !== 'public') {
            return response()->json(['message' => 'This endpoint only joins public fantasy leagues.'], 422);
        }

        try {
            $membership = $this->membershipService->join(
                $fantasyLeague,
                $request->user(),
                $request->input('team_name')
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Joined fantasy league successfully.',
            'data' => new MembershipResource($membership),
        ], 201);
    }

    public function members(Request $request, FantasyLeague $fantasyLeague): AnonymousResourceCollection|JsonResponse
    {
        if (! $this->isMember($request, $fantasyLeague)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $memberships = $fantasyLeague->memberships()
            ->with(['user', 'fantasyTeam'])
            ->get();

        return MembershipResource::collection($memberships);
    }

    public function standings(Request $request, FantasyLeague $fantasyLeague, Week $week): AnonymousResourceCollection|JsonResponse
    {
        if ($fantasyLeague->competition_id !== $week->competition_id) {
            return response()->json(['message' => 'Week does not belong to the same competition.'], 422);
        }

        if (! $this->isMember($request, $fantasyLeague)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $scores = $week->scores()
            ->whereHas('fantasyTeam.membership', fn ($q) => $q->where('fantasy_league_id', $fantasyLeague->id))
            ->with(['fantasyTeam.membership'])
            ->orderBy('rank')
            ->get();

        return FantasyTeamScoreResource::collection($scores);
    }

    // check if the user can access a private league
    private function isMember(Request $request, FantasyLeague $league): bool
    {
        return $league->visibility === 'public'
            || ($request->user() !== null
                && $league->memberships()->where('user_id', $request->user()->id)->exists());
    }
}
