@extends('layouts.app')

@section('title', data_get($match, 'teams.0.tag', 'Match').' vs '.data_get($match, 'teams.1.tag', 'Match').' · League of Fantasy')

@section('content')
    <section class="space-y-2">
        <h1 class="text-xl font-semibold">{{ data_get($match, 'teams.0.tag', '—') }} vs {{ data_get($match, 'teams.1.tag', '—') }}</h1>
        <p class="text-sm text-base-content/70">Week {{ $week['number'] }} · {{ $match['status'] }}</p>
    </section>

    <section class="space-y-3">
        <h2 class="text-base font-medium">Player statistics</h2>
        <div class="overflow-x-auto border border-base-300 rounded-lg">
            <table class="table">
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Role</th>
                        <th>Kills</th>
                        <th>Deaths</th>
                        <th>Assists</th>
                        <th class="font-medium">Fantasy pts</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stats as $stat)
                        <tr>
                            <td>{{ data_get($stat, 'player.nickname') }}</td>
                            <td>{{ data_get($stat, 'player.role') }}</td>
                            <td class="tabular-nums">{{ $stat['kills'] }}</td>
                            <td class="tabular-nums">{{ $stat['deaths'] }}</td>
                            <td class="tabular-nums">{{ $stat['assists'] }}</td>
                            <td class="tabular-nums">{{ rtrim(rtrim(number_format((float) $stat['fantasy_points'], 2), '0'), '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
