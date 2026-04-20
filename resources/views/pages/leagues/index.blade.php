@extends('layouts.app')

@section('title', 'Leagues · League of Fantasy')

@section('content')
    <section class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
            <h1 class="text-xl font-semibold">Leagues</h1>
        </div>

        @if (session('api_token'))
            <div class="flex items-center gap-3">
                <a href="{{ route('leagues.create') }}" class="btn btn-sm btn-primary">Create league</a>
                <a href="{{ route('leagues.joinPrivateForm') }}" class="text-sm hover:text-primary transition-colors">Join private league →</a>
            </div>
        @endif
    </section>

    @if ($leagues === [])
        <p class="text-sm text-base-content/70">No leagues yet.</p>
    @else
        <section class="overflow-x-auto border border-base-300 rounded-lg">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Competition</th>
                        <th>Visibility</th>
                        <th>Members</th>
                        <th>Budget cap</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leagues as $league)
                        <tr>
                            <td>{{ $league['name'] }}</td>
                            <td>{{ data_get($league, 'competition.name') }}</td>
                            <td>
                                <span class="badge {{ $league['visibility'] === 'private' ? 'badge-neutral' : 'badge-ghost' }}">
                                    {{ $league['visibility'] }}
                                </span>
                            </td>
                            <td class="tabular-nums">{{ $league['memberships_count'] }}/{{ $league['max_participants'] }}</td>
                            <td class="tabular-nums">{{ rtrim(rtrim(number_format((float) $league['budget_cap'], 2), '0'), '.') }} pts</td>
                            <td>{{ $league['status'] }}</td>
                            <td class="text-right">
                                @if (session('api_token'))
                                    <a href="{{ route('leagues.show', $league['id']) }}" class="hover:text-primary transition-colors">Open →</a>
                                @else
                                    <a href="{{ route('auth.loginForm') }}" class="hover:text-primary transition-colors">Open →</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif
@endsection
