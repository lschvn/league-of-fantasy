@extends('layouts.app')

@section('title', $league['name'].' · League of Fantasy')

@section('content')
    @php
        $tabItems = [
            ['label' => 'Overview', 'href' => route('leagues.show', ['fantasyLeague' => $league['id'], 'tab' => 'overview']), 'active' => $activeTab === 'overview'],
            ['label' => 'Members', 'href' => route('leagues.show', ['fantasyLeague' => $league['id'], 'tab' => 'members']), 'active' => $activeTab === 'members'],
            ['label' => 'Standings', 'href' => route('leagues.show', ['fantasyLeague' => $league['id'], 'tab' => 'standings']), 'active' => $activeTab === 'standings'],
            ['label' => 'Auctions', 'href' => route('leagues.show', ['fantasyLeague' => $league['id'], 'tab' => 'auctions']), 'active' => $activeTab === 'auctions'],
        ];
        $membersSorted = collect($members)
            ->sortBy(fn (array $member) => data_get($member, 'role') === 'owner' ? 0 : 1)
            ->values();
    @endphp

    <section class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
            <h1 class="text-xl font-semibold">{{ $league['name'] }}</h1>
            <p class="text-sm text-base-content/70">
                {{ data_get($league, 'competition.name') }} · {{ $league['visibility'] }} · {{ $league['status'] }}
            </p>
        </div>

        @if (! $currentMembership && $league['visibility'] === 'public')
            <form method="POST" action="{{ route('leagues.join', $league['id']) }}">
                @csrf
                <button type="submit" class="btn btn-primary">Join</button>
            </form>
        @endif
    </section>

    @include('partials.tabs', ['items' => $tabItems])

    @if ($activeTab === 'overview')
        <section class="grid gap-6 md:grid-cols-2">
            <div class="border border-base-300 rounded-lg">
                <div class="border-b border-base-300 px-4 py-3">
                    <h2 class="text-base font-medium">League details</h2>
                </div>
                <dl class="px-4 py-3 space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-base-content/70">Budget cap</dt>
                        <dd class="tabular-nums">{{ rtrim(rtrim(number_format((float) $league['budget_cap'], 2), '0'), '.') }} pts</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-base-content/70">Max participants</dt>
                        <dd class="tabular-nums">{{ $league['max_participants'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-base-content/70">Join deadline</dt>
                        <dd>@uiDate($league['join_deadline'])</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-base-content/70">Scoring rule version</dt>
                        <dd>{{ $league['scoring_rule_version'] ?: '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="border border-base-300 rounded-lg">
                <div class="border-b border-base-300 px-4 py-3">
                    <h2 class="text-base font-medium">Your team</h2>
                </div>
                <div class="px-4 py-3 space-y-2">
                    @if (is_array(data_get($currentMembership, 'fantasy_team')))
                        <p>{{ data_get($currentMembership, 'fantasy_team.name') }}</p>
                        <p class="text-sm text-base-content/70">
                            Budget remaining: {{ rtrim(rtrim(number_format((float) data_get($currentMembership, 'fantasy_team.budget_remaining'), 2), '0'), '.') }} pts
                        </p>
                        <a href="{{ route('teams.show', data_get($currentMembership, 'fantasy_team.id')) }}" class="text-sm hover:text-primary transition-colors">
                            View team →
                        </a>
                    @else
                        <p class="text-sm text-base-content/70">You don't have a team in this league yet.</p>
                    @endif
                </div>
            </div>
        </section>

        @if ($isOwner)
            <section class="border border-base-300 rounded-lg">
                <div class="border-b border-base-300 px-4 py-3">
                    <h2 class="text-base font-medium">Invitations</h2>
                </div>
                <div class="px-4 py-4 space-y-6">
                    <form method="POST" action="{{ route('invitations.store', $league['id']) }}" class="grid gap-3 md:grid-cols-[1fr_140px_auto] md:items-end">
                        @csrf
                        <div class="form-control">
                            <label for="expires_at" class="label">
                                <span class="label-text">Expires at</span>
                            </label>
                            <input id="expires_at" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="input input-bordered w-full">
                            @error('expires_at')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-control">
                            <label for="max_uses" class="label">
                                <span class="label-text">Max uses</span>
                            </label>
                            <input id="max_uses" type="number" name="max_uses" value="{{ old('max_uses', 1) }}" min="1" class="input input-bordered w-full">
                            @error('max_uses')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Create invitation</button>
                    </form>

                    @if ($knownInvitations === [])
                        <p class="text-sm text-base-content/70">No invitation links created in this session yet.</p>
                    @else
                        <div class="overflow-x-auto border border-base-300 rounded-lg">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Expires</th>
                                        <th>Uses</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($knownInvitations as $invitation)
                                        <tr>
                                            <td class="font-mono text-sm">{{ $invitation['code'] }}</td>
                                            <td>@uiDate($invitation['expires_at'])</td>
                                            <td class="tabular-nums">{{ $invitation['used_count'] }}/{{ $invitation['max_uses'] }}</td>
                                            <td>{{ $invitation['is_valid'] ? 'active' : 'inactive' }}</td>
                                            <td class="text-right">
                                                <form method="POST" action="{{ route('invitations.destroy', $invitation['id']) }}" onsubmit="return confirm('Revoke this invitation?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="fantasy_league_id" value="{{ $league['id'] }}">
                                                    <button type="submit" class="btn btn-xs btn-error btn-outline">Revoke</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @elseif ($activeTab === 'members')
        <section class="overflow-x-auto border border-base-300 rounded-lg">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($membersSorted as $member)
                        <tr>
                            <td>{{ data_get($member, 'user.name') }}</td>
                            <td>
                                <span class="badge {{ data_get($member, 'role') === 'owner' ? 'badge-primary' : 'badge-ghost' }}">
                                    {{ data_get($member, 'role') }}
                                </span>
                            </td>
                            <td>{{ data_get($member, 'status') }}</td>
                            <td>@uiDate(data_get($member, 'joined_at'))</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @elseif ($activeTab === 'standings')
        <section class="space-y-4">
            <form method="GET" action="{{ route('leagues.show', $league['id']) }}" class="flex items-center gap-3">
                <input type="hidden" name="tab" value="standings">
                <select name="week" class="select select-sm select-bordered">
                    @foreach ($weeks as $week)
                        <option value="{{ $week['id'] }}" @selected((int) $selectedWeekId === (int) $week['id'])>
                            Week {{ $week['number'] }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm">Load</button>
            </form>

            @if ($standingsError)
                <div class="alert alert-error rounded-lg">
                    <span>{{ $standingsError }}</span>
                </div>
            @endif

            @if ($standings === [])
                <p class="text-sm text-base-content/70">No scores yet.</p>
            @else
                <div class="overflow-x-auto border border-base-300 rounded-lg">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Team</th>
                                <th>Points</th>
                                <th>Calculated at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($standings as $standing)
                                <tr>
                                    <td class="tabular-nums">{{ $standing['rank'] }}</td>
                                    <td>{{ data_get($standing, 'fantasy_team.name') }}</td>
                                    <td class="tabular-nums">{{ rtrim(rtrim(number_format((float) $standing['points'], 2), '0'), '.') }}</td>
                                    <td>@uiDate($standing['calculated_at'])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @else
        <section class="space-y-3">
            @if ($knownAuctions === [])
                <p class="text-sm text-base-content/70">No auction rooms are available from the current API context.</p>
            @else
                <div class="border border-base-300 rounded-lg divide-y divide-base-300">
                    @foreach ($knownAuctions as $auction)
                        <a href="{{ route('auctions.show', $auction['id']) }}" class="px-4 py-3 flex items-center justify-between gap-4 hover:text-primary transition-colors">
                            <div class="space-y-1">
                                <p>Auction #{{ $auction['id'] }}</p>
                                <p class="text-sm text-base-content/70">Week {{ data_get($auction, 'week.number') }} · {{ $auction['status'] }}</p>
                            </div>
                            <span class="text-sm">Open →</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
@endsection
