<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invitation\JoinPrivateLeagueRequest;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Models\FantasyLeague;
use App\Models\Invitation;
use App\Services\LeagueMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvitationController extends Controller
{
    public function __construct(
        private readonly LeagueMembershipService $membershipService
    ) {
    }

    public function store(StoreInvitationRequest $request, FantasyLeague $fantasyLeague): JsonResponse
    {
        $membership = $fantasyLeague->memberships()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $membership || ! $membership->isManager()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($fantasyLeague->visibility !== 'private') {
            return response()->json(['message' => 'Invitations are only for private fantasy leagues.'], 422);
        }

        $invitation = Invitation::create([
            'fantasy_league_id' => $fantasyLeague->id,
            'code' => Str::upper(Str::random(10)),
            'expires_at' => $request->date('expires_at'),
            'max_uses' => $request->integer('max_uses', 1),
            'used_count' => 0,
        ]);

        return response()->json([
            'message' => 'Invitation created successfully.',
            'data' => $invitation,
        ], 201);
    }

    public function join(JoinPrivateLeagueRequest $request): JsonResponse
    {
        $invitation = Invitation::query()
            ->where('code', strtoupper($request->string('code')))
            ->with('fantasyLeague')
            ->first();

        if (! $invitation) {
            return response()->json(['message' => 'Invitation not found.'], 404);
        }

        if (! $invitation->isValid()) {
            return response()->json(['message' => 'Invitation is no longer valid.'], 422);
        }

        try {
            $membership = DB::transaction(function () use ($request, $invitation) {
                $membership = $this->membershipService->join(
                    $invitation->fantasyLeague,
                    $request->user(),
                    $request->input('team_name')
                );

                $invitation->increment('used_count');

                return $membership;
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Joined private fantasy league successfully.',
            'data' => $membership,
        ], 201);
    }

    public function revoke(Request $request, Invitation $invitation): JsonResponse
    {
        $membership = $invitation->fantasyLeague->memberships()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $membership || ! $membership->isManager()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $invitation->revoke();

        return response()->json(['message' => 'Invitation revoked successfully.']);
    }
}
