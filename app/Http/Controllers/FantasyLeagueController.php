<?php

namespace App\Http\Controllers;

use App\Http\Requests\FantasyLeague\JoinPublicFantasyLeagueRequest;
use App\Http\Requests\FantasyLeague\StoreFantasyLeagueRequest;
use App\Http\Resources\AuctionResource;
use App\Http\Resources\FantasyLeagueResource;
use App\Http\Resources\FantasyTeamScoreResource;
use App\Http\Resources\MembershipResource;
use App\Models\FantasyLeague;
use App\Models\Week;
use App\Services\LeagueMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FantasyLeagueController extends Controller
{
    public function __construct(
        private readonly LeagueMembershipService $membershipService
    ) {}

    /**
     * list fantasy leagues available for review.
     *
     * @unauthenticated
     */
    public function index(Request $request): JsonResponse
    {
        $query = FantasyLeague::query()
            ->with('competition')
            ->withCount('memberships')
            ->latest();

        if ($request->boolean('public_only', true)) {
            $query->where('visibility', 'public');
        }

        return $this->successResponse(
            'fantasy leagues fetched successfully.',
            FantasyLeagueResource::collection($query->get())
        );
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

        return $this->successResponse(
            'fantasy league created successfully.',
            new FantasyLeagueResource($league->load(['competition', 'creator'])),
            201
        );
    }

    public function show(Request $request, FantasyLeague $fantasyLeague): JsonResponse
    {
        if (! $this->isMember($request, $fantasyLeague)) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse(
            'fantasy league fetched successfully.',
            new FantasyLeagueResource(
                $fantasyLeague->load(['competition', 'creator'])->loadCount('memberships')
            )
        );
    }

    public function join(JoinPublicFantasyLeagueRequest $request, FantasyLeague $fantasyLeague): JsonResponse
    {
        if ($fantasyLeague->visibility !== 'public') {
            return $this->unprocessableResponse('this endpoint only joins public fantasy leagues.');
        }

        try {
            $membership = $this->membershipService->join(
                $fantasyLeague,
                $request->user(),
                $request->input('team_name')
            );
        } catch (RuntimeException $e) {
            return $this->unprocessableResponse($e->getMessage());
        }

        return $this->successResponse(
            'joined fantasy league successfully.',
            new MembershipResource($membership->load(['user', 'fantasyTeam'])),
            201
        );
    }

    public function members(Request $request, FantasyLeague $fantasyLeague): JsonResponse
    {
        if (! $this->isMember($request, $fantasyLeague)) {
            return $this->forbiddenResponse();
        }

        $memberships = $fantasyLeague->memberships()
            ->with(['user', 'fantasyTeam'])
            ->get();

        return $this->successResponse(
            'fantasy league members fetched successfully.',
            MembershipResource::collection($memberships)
        );
    }

    /**
     * list auctions for a fantasy league.
     *
     * @authenticated
     *
     * @urlParam fantasyLeague integer required The fantasy league ID. Example: 1
     */
    public function auctions(Request $request, FantasyLeague $fantasyLeague): JsonResponse
    {
        if (! $this->isMember($request, $fantasyLeague)) {
            return $this->forbiddenResponse();
        }

        $auctions = $fantasyLeague->auctions()
            ->with('week')
            ->latest('start_at')
            ->get();

        return $this->successResponse(
            'fantasy league auctions fetched successfully.',
            AuctionResource::collection($auctions)
        );
    }

    public function standings(Request $request, FantasyLeague $fantasyLeague, Week $week): JsonResponse
    {
        if ($fantasyLeague->competition_id !== $week->competition_id) {
            return $this->unprocessableResponse('week does not belong to the same competition.');
        }

        if (! $this->isMember($request, $fantasyLeague)) {
            return $this->forbiddenResponse();
        }

        $scores = $week->scores()
            ->whereHas('fantasyTeam.membership', fn ($q) => $q->where('fantasy_league_id', $fantasyLeague->id))
            ->with(['fantasyTeam.membership.user'])
            ->orderBy('rank')
            ->get();

        return $this->successResponse(
            'fantasy league standings fetched successfully.',
            FantasyTeamScoreResource::collection($scores)
        );
    }

    // check whether the current user can access the target league
    private function isMember(Request $request, FantasyLeague $league): bool
    {
        $user = $request->user('sanctum') ?? $request->user();

        return $league->visibility === 'public'
            || ($user !== null
                && $league->memberships()->where('user_id', $user->id)->exists());
    }
}
