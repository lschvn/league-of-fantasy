@extends('layouts.app')

@section('title', 'Competitions · League of Fantasy')

@section('content')
    <section class="space-y-2">
        <h1 class="text-xl font-semibold">Competitions</h1>
        <p class="text-sm text-base-content/70">Real-world esports competitions you can build fantasy leagues around.</p>
    </section>

    <section class="overflow-x-auto border border-base-300 rounded-lg">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Region</th>
                    <th>Season</th>
                    <th>Teams</th>
                    <th>Weeks</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($competitions as $competition)
                    <tr>
                        <td>{{ $competition['name'] }}</td>
                        <td>{{ $competition['region'] }}</td>
                        <td>{{ $competition['season'] }}</td>
                        <td class="tabular-nums">{{ $competition['teams_count'] }}</td>
                        <td class="tabular-nums">{{ $competition['weeks_count'] }}</td>
                        <td class="text-right">
                            <a href="{{ route('competitions.show', $competition['id']) }}" class="hover:text-primary transition-colors">View →</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
