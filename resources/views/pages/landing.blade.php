@extends('layouts.app')

@section('title', 'League of Fantasy')

@section('content')
    <section class="space-y-3">
        <h1 class="text-2xl font-semibold">Fantasy esports leagues, without the noise.</h1>
        <p class="max-w-2xl text-sm text-base-content/70">
            A demo fantasy platform for tracking esports competitions, leagues, teams, lineups, and auctions.
        </p>
    </section>

    <div class="divider"></div>

    <section class="grid gap-6 md:grid-cols-2">
        <div class="space-y-3">
            <div class="border border-base-300 rounded-lg">
                <div class="border-b border-base-300 px-4 py-3">
                    <h2 class="text-base font-medium">Ongoing competitions</h2>
                </div>
                <ul class="divide-y divide-base-300">
                    @forelse ($competitions as $competition)
                        <li class="px-4 py-3">
                            <a href="{{ route('competitions.show', $competition['id']) }}" class="block space-y-1 hover:text-primary transition-colors">
                                <div>{{ $competition['name'] }}</div>
                                <p class="text-sm text-base-content/70">{{ $competition['region'] }} · {{ $competition['season'] }}</p>
                            </a>
                        </li>
                    @empty
                        <li class="px-4 py-3 text-sm text-base-content/70">No competitions are available right now.</li>
                    @endforelse
                </ul>
                <div class="border-t border-base-300 px-4 py-3">
                    <a href="{{ route('competitions.index') }}" class="text-sm hover:text-primary transition-colors">See all →</a>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="border border-base-300 rounded-lg">
                <div class="border-b border-base-300 px-4 py-3">
                    <h2 class="text-base font-medium">Public fantasy leagues</h2>
                </div>
                <ul class="divide-y divide-base-300">
                    @forelse ($leagues as $league)
                        <li class="px-4 py-3">
                            <a href="{{ route('leagues.index') }}" class="block space-y-1 hover:text-primary transition-colors">
                                <div>{{ $league['name'] }}</div>
                                <p class="text-sm text-base-content/70">
                                    {{ data_get($league, 'competition.name') }} · {{ $league['memberships_count'] }}/{{ $league['max_participants'] }} members
                                </p>
                            </a>
                        </li>
                    @empty
                        <li class="px-4 py-3 text-sm text-base-content/70">No public leagues are available right now.</li>
                    @endforelse
                </ul>
                <div class="border-t border-base-300 px-4 py-3">
                    <a href="{{ route('leagues.index') }}" class="text-sm hover:text-primary transition-colors">Browse all →</a>
                </div>
            </div>
        </div>
    </section>
@endsection
