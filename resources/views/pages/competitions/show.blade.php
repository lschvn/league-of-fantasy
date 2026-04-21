@extends('layouts.app')

@section('title', $competition['name'].' · League of Fantasy')

@section('content')
    <section class="space-y-2">
        <h1 class="text-xl font-semibold">{{ $competition['name'] }}</h1>
        <p class="text-sm text-base-content/70">{{ $competition['region'] }} · {{ $competition['season'] }}</p>
    </section>

    <div class="divider"></div>

    <section class="space-y-3">
        <h2 class="text-base font-medium">Teams</h2>
        <div class="border border-base-300 rounded-lg divide-y divide-base-300">
            @foreach ($competition['teams'] as $team)
                @php
                    $players = collect(data_get($team, 'players', []))
                        ->sortBy('nickname')
                        ->values();
                @endphp
                <div class="px-4 py-3 space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="font-mono text-xs bg-base-200 rounded px-1.5 py-0.5">{{ $team['tag'] }}</span>
                            <span class="truncate">{{ $team['name'] }}</span>
                        </div>
                        <p class="text-sm text-base-content/70 whitespace-nowrap">{{ $players->count() }} players</p>
                    </div>

                    @if ($players->isEmpty())
                        <p class="text-sm text-base-content/70">No players listed.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($players as $player)
                                <span class="badge badge-ghost gap-1">
                                    <span class="font-mono text-[10px]">{{ data_get($player, 'role', '—') }}</span>
                                    <span>{{ data_get($player, 'nickname') }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <div class="divider"></div>

    <section class="space-y-3">
        <h2 class="text-base font-medium">Weeks</h2>
        <div class="overflow-x-auto border border-base-300 rounded-lg">
            <table class="table">
                <thead>
                    <tr>
                        <th>Week</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Lineup lock</th>
                        <th>Matches</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($weeks as $week)
                        <tr>
                            <td class="tabular-nums">{{ $week['number'] }}</td>
                            <td>@uiDate($week['start_at'])</td>
                            <td>@uiDate($week['end_at'])</td>
                            <td>@uiDate($week['lineup_lock_at'])</td>
                            <td class="tabular-nums">{{ $week['matches_count'] }}</td>
                            <td class="text-right">
                                <a href="{{ route('weeks.show', $week['id']) }}" class="hover:text-primary transition-colors">View matches →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
