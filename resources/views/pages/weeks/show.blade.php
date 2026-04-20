@extends('layouts.app')

@section('title', 'Week '.$week['number'].' · League of Fantasy')

@section('content')
    <section class="space-y-2">
        <h1 class="text-xl font-semibold">Week {{ $week['number'] }}</h1>
        <p class="text-sm text-base-content/70">@uiDate($week['start_at']) → @uiDate($week['end_at'])</p>
    </section>

    <section class="overflow-x-auto border border-base-300 rounded-lg">
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
                        <td class="font-mono text-sm">{{ $match['id'] }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs">{{ data_get($match, 'teams.0.tag', '—') }}</span>
                                <span class="text-base-content/60">vs</span>
                                <span class="font-mono text-xs">{{ data_get($match, 'teams.1.tag', '—') }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeClass = match ($match['status']) {
                                    'live' => 'badge badge-primary',
                                    'completed' => 'badge badge-neutral',
                                    default => 'badge badge-ghost',
                                };
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $match['status'] }}</span>
                        </td>
                        <td>@uiDate($match['started_at'])</td>
                        <td>@uiDate($match['ended_at'])</td>
                        <td class="text-right">
                            <a href="{{ route('matches.show', $match['id']) }}" class="hover:text-primary transition-colors">View stats →</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
