@extends('layouts.app')

@section('title', $team['name'].' · League of Fantasy')

@section('content')
    @php
        $tabItems = [
            ['label' => 'Roster', 'href' => route('teams.show', ['team' => $team['id'], 'tab' => 'roster']), 'active' => $activeTab === 'roster'],
            ['label' => 'Lineups', 'href' => route('teams.show', ['team' => $team['id'], 'tab' => 'lineups']), 'active' => $activeTab === 'lineups'],
        ];
    @endphp

    <section class="space-y-2">
        <h1 class="text-xl font-semibold">{{ $team['name'] }}</h1>
        <p class="text-sm text-base-content/70">
            {{ $league['name'] }} · Budget remaining: {{ rtrim(rtrim(number_format((float) $team['budget_remaining'], 2), '0'), '.') }} pts
        </p>
    </section>

    @include('partials.tabs', ['items' => $tabItems])

    @if ($activeTab === 'roster')
        <section class="overflow-x-auto border border-base-300 rounded-lg">
            <table class="table">
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Role</th>
                        <th>Acquired at</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roster as $slot)
                        <tr>
                            <td>{{ data_get($slot, 'player.nickname') }}</td>
                            <td>{{ data_get($slot, 'player.role') }}</td>
                            <td>@uiDate($slot['acquired_at'])</td>
                            <td class="tabular-nums">{{ rtrim(rtrim(number_format((float) $slot['acquisition_cost'], 2), '0'), '.') }}</td>
                            <td>{{ $slot['status'] }}</td>
                            <td class="text-right">
                                @if ($slot['status'] === 'active' && ! in_array((int) $slot['id'], $lockedRosterSlotIds, true))
                                    <form method="POST" action="{{ route('roster.destroy', ['team' => $team['id'], 'rosterSlot' => $slot['id']]) }}" onsubmit="return confirm('Release this player from the roster?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-error btn-outline">Release</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @else
        <section class="border border-base-300 rounded-lg divide-y divide-base-300">
            @foreach ($weeks as $week)
                <div class="px-4 py-3 flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <p>Week {{ $week['number'] }}</p>
                        <p class="text-sm text-base-content/70">Lineup lock: @uiDate($week['lineup_lock_at'])</p>
                    </div>
                    <a href="{{ route('lineups.edit', ['team' => $team['id'], 'week' => $week['id']]) }}" class="text-sm hover:text-primary transition-colors">
                        View lineup →
                    </a>
                </div>
            @endforeach
        </section>
    @endif
@endsection
