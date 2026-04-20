@extends('layouts.app')

@section('title', 'Dashboard · League of Fantasy')

@section('content')
    <section class="space-y-2">
        <h1 class="text-xl font-semibold">Dashboard</h1>
    </section>

    <section id="my-leagues" class="space-y-3">
        <h2 class="text-base font-medium">My leagues</h2>
        @if ($memberships === [])
            <p class="text-sm text-base-content/70">You are not in any leagues yet.</p>
        @else
            <div class="overflow-x-auto border border-base-300 rounded-lg">
                <table class="table">
                    <thead>
                        <tr>
                            <th>League</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($memberships as $membership)
                            <tr>
                                <td>{{ data_get($membership, 'fantasy_league.name') }}</td>
                                <td>
                                    <span class="badge {{ data_get($membership, 'role') === 'owner' ? 'badge-primary' : 'badge-ghost' }}">
                                        {{ data_get($membership, 'role') }}
                                    </span>
                                </td>
                                <td>{{ data_get($membership, 'status') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('leagues.show', data_get($membership, 'fantasy_league_id')) }}" class="hover:text-primary transition-colors">Open →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section id="my-teams" class="space-y-3">
        <h2 class="text-base font-medium">My teams</h2>
        @if ($teams === [])
            <p class="text-sm text-base-content/70">You do not have any teams yet.</p>
        @else
            <div class="overflow-x-auto border border-base-300 rounded-lg">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Team</th>
                            <th>League</th>
                            <th>Budget remaining</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teams as $team)
                            <tr>
                                <td>{{ $team['name'] }}</td>
                                <td>{{ data_get($team, 'fantasy_league.name') }}</td>
                                <td class="tabular-nums">{{ rtrim(rtrim(number_format((float) $team['budget_remaining'], 2), '0'), '.') }} pts</td>
                                <td class="text-right">
                                    <a href="{{ route('teams.show', $team['id']) }}" class="hover:text-primary transition-colors">Open →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="space-y-3">
        <h2 class="text-base font-medium">Quick actions</h2>
        <p class="text-sm text-base-content/70">
            <a href="{{ route('leagues.create') }}" class="hover:text-primary transition-colors">Create league</a>
            ·
            <a href="{{ route('leagues.joinPrivateForm') }}" class="hover:text-primary transition-colors">Join private league</a>
            ·
            <a href="{{ route('leagues.index') }}" class="hover:text-primary transition-colors">Browse public leagues</a>
        </p>
    </section>
@endsection
