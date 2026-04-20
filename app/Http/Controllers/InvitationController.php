<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invitation\JoinPrivateLeagueRequest;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Http\Resources\InvitationResource;
use App\Http\Resources\MembershipResource;
use App\Models\FantasyLeague;
use App\Models\Invitation;
use App\Services\LeagueMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InvitationController extends Controller
{
    public function __construct(
        private readonly LeagueMembershipService $membershipService
    ) {}

    public function store(StoreInvitationRequest $request, FantasyLeague $fantasyLeague): JsonResponse
    {
        $membership = $fantasyLeague->memberships()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $membership || ! $membership->isManager()) {
            return $this->forbiddenResponse();
        }

        if ($fantasyLeague->visibility !== 'private') {
            return $this->unprocessableResponse('invitations are only available for private fantasy leagues.');
        }

        $invitation = Invitation::create([
            'fantasy_league_id' => $fantasyLeague->id,
            'code' => Str::upper(Str::random(10)),
            'expires_at' => $request->date('expires_at'),
            'max_uses' => $request->integer('max_uses', 1),
            'used_count' => 0,
        ]);

        return $this->successResponse(
            'invitation created successfully.',
            new InvitationResource($invitation->load('fantasyLeague')),
            201
        );
    }

    public function join(JoinPrivateLeagueRequest $request): JsonResponse
    {
        $invitation = Invitation::query()
            ->where('code', strtoupper($request->string('code')))
            ->with('fantasyLeague')
            ->first();

        if (! $invitation) {
            return $this->notFoundResponse('invitation not found.');
        }

        if (! $invitation->isValid()) {
            return $this->unprocessableResponse('invitation is no longer valid.');
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
            return $this->unprocessableResponse($e->getMessage());
        }

        return $this->successResponse(
            'joined private fantasy league successfully.',
            new MembershipResource($membership->load(['user', 'fantasyTeam'])),
            201
        );
    }

    public function revoke(Request $request, Invitation $invitation): JsonResponse
    {
        $membership = $invitation->fantasyLeague->memberships()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $membership || ! $membership->isManager()) {
            return $this->forbiddenResponse();
        }

        $invitation->revoke();

        return $this->successResponse(
            'invitation revoked successfully.',
            new InvitationResource($invitation->fresh()->load('fantasyLeague'))
        );
    }
}
