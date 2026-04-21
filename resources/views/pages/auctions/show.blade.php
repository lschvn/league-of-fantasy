@extends('layouts.app')

@section('title', 'Auction #'.$auction['id'].' · League of Fantasy')

@section('content')
    <section class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold">Auction #{{ $auction['id'] }}</h1>
                <span class="badge {{ $auction['is_open'] ? 'badge-primary' : 'badge-neutral' }}">
                    {{ $auction['is_open'] ? 'Open' : 'Closed' }}
                </span>
            </div>
            <p class="text-sm text-base-content/70">
                Week {{ data_get($auction, 'week.number') }} · {{ $auction['status'] }} · @uiDate($auction['start_at']) → @uiDate($auction['end_at'])
            </p>
            <p class="text-sm text-base-content/70">Ends at @uiDate($auction['end_at'])</p>
        </div>

        @if ($auction['is_open'] && $isOwner)
            <form method="POST" action="{{ route('auctions.close', $auction['id']) }}" onsubmit="return confirm('Close this auction?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-error btn-outline">Close auction</button>
            </form>
        @endif
    </section>

    <section class="grid gap-6 md:grid-cols-3">
        <div class="space-y-3 md:col-span-2">
            <h2 class="text-base font-medium">Bids</h2>
            <div class="overflow-x-auto border border-base-300 rounded-lg">
                <table class="table">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Team</th>
                            <th>Player</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bids as $bid)
                            <tr>
                                <td>@uiDate($bid['placed_at'])</td>
                                <td>{{ data_get($membership, 'fantasy_team.name') }}</td>
                                <td>{{ data_get($bid, 'player.nickname') }}</td>
                                <td class="tabular-nums">{{ rtrim(rtrim(number_format((float) $bid['amount'], 2), '0'), '.') }}</td>
                                <td>{{ $bid['status'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-sm text-base-content/70">No bids yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3">
            <h2 class="text-base font-medium">Place a bid</h2>
            @if ($auction['is_open'] && is_array(data_get($membership, 'fantasy_team')))
                <form method="POST" action="{{ route('bids.store', $auction['id']) }}" class="border border-base-300 rounded-lg p-4 space-y-3">
                    @csrf
                    <input type="hidden" name="fantasy_team_id" value="{{ data_get($membership, 'fantasy_team.id') }}">

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Fantasy team</span>
                        </label>
                        <div class="border border-base-300 rounded-lg px-3 py-2 text-sm bg-base-200">
                            {{ data_get($membership, 'fantasy_team.name') }}
                        </div>
                        @error('fantasy_team_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label for="player_id" class="label">
                            <span class="label-text">Player</span>
                        </label>
                        <select id="player_id" name="player_id" class="select select-bordered w-full" @disabled($players === []) required>
                            @if ($players === [])
                                <option value="">No players available</option>
                            @else
                                <option value="">Select a player</option>
                                @foreach ($players as $player)
                                    @php
                                        $teamId = (string) data_get($player, 'team.id', data_get($player, 'team_id', ''));
                                        $teamLabel = data_get($player, 'team.tag')
                                            ?: data_get($player, 'team.name')
                                            ?: data_get($teamsById, $teamId.'.tag')
                                            ?: data_get($teamsById, $teamId.'.name')
                                            ?: (filled($teamId) ? 'Team #'.$teamId : 'Unknown team');
                                        $nickname = data_get($player, 'nickname') ?: 'Player #'.data_get($player, 'id');
                                    @endphp
                                    <option value="{{ $player['id'] }}" @selected((int) old('player_id') === (int) $player['id'])>
                                        {{ $teamLabel }} · {{ data_get($player, 'role', '—') }} · {{ $nickname }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @if ($playersError)
                            <p class="text-warning text-sm mt-1">{{ $playersError }}</p>
                        @elseif ($players === [])
                            <p class="text-sm text-base-content/70 mt-1">No players are currently available for this auction.</p>
                        @endif
                        @error('player_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label for="amount" class="label">
                            <span class="label-text">Amount</span>
                        </label>
                        <input id="amount" type="number" name="amount" value="{{ old('amount') }}" min="1" step="1" class="input input-bordered w-full tabular-nums" required>
                        @error('amount')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Place bid</button>
                </form>
            @else
                <p class="text-sm text-base-content/70">You cannot place a bid in this auction right now.</p>
            @endif
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="text-base font-medium">Related matches</h2>

        @if ($matchesError)
            <div class="alert alert-error rounded-lg">
                <span>{{ $matchesError }}</span>
            </div>
        @endif

        @if ($matches === [])
            <p class="text-sm text-base-content/70">No matches are scheduled for this auction week.</p>
        @else
            <div class="overflow-x-auto border border-base-300 rounded-lg">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Match ID</th>
                            <th>Teams</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Ended</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($matches as $match)
                            <tr>
                                <td class="font-mono text-sm">{{ data_get($match, 'id') }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs">{{ data_get($match, 'teams.0.tag', '—') }}</span>
                                        <span class="text-base-content/60">vs</span>
                                        <span class="font-mono text-xs">{{ data_get($match, 'teams.1.tag', '—') }}</span>
                                    </div>
                                </td>
                                <td>{{ data_get($match, 'status') }}</td>
                                <td>@uiDate(data_get($match, 'started_at'))</td>
                                <td>@uiDate(data_get($match, 'ended_at'))</td>
                                <td class="text-right">
                                    <a href="{{ route('matches.show', data_get($match, 'id')) }}" class="hover:text-primary transition-colors">View stats →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
