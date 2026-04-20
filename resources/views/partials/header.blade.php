@php
    $currentUser = session('api_user');
    $isAuthenticated = filled(session('api_token'));
    $navItems = [
        ['label' => 'Competitions', 'route' => route('competitions.index'), 'active' => request()->routeIs('competitions.*') || request()->routeIs('weeks.*') || request()->routeIs('matches.*')],
        ['label' => 'Leagues', 'route' => route('leagues.index'), 'active' => request()->routeIs('leagues.*')],
    ];

    if ($isAuthenticated) {
        $navItems[] = ['label' => 'Dashboard', 'route' => route('dashboard.index'), 'active' => request()->routeIs('dashboard.*')];
    }
@endphp

<header class="sticky top-0 z-20 border-b border-base-300 bg-base-100">
    <div class="max-w-6xl mx-auto h-14 px-6 flex items-center justify-between gap-4">
        <a href="{{ route('landing') }}" class="font-semibold text-base whitespace-nowrap">
            League of Fantasy
        </a>

        <nav class="hidden md:flex flex-1 justify-center">
            <ul class="menu menu-horizontal gap-1 px-1">
                @foreach ($navItems as $item)
                    <li>
                        <a href="{{ $item['route'] }}" class="{{ $item['active'] ? 'font-medium' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-3">
            <div class="dropdown dropdown-end md:hidden">
                <label tabindex="0" class="btn btn-ghost btn-sm">Menu</label>
                <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-48 rounded-lg border border-base-300 bg-base-100 p-2 shadow">
                    @foreach ($navItems as $item)
                        <li><a href="{{ $item['route'] }}">{{ $item['label'] }}</a></li>
                    @endforeach
                    @unless ($isAuthenticated)
                        <li><a href="{{ route('auth.loginForm') }}">Log in</a></li>
                    @endunless
                </ul>
            </div>

            @if (! $isAuthenticated)
                <a href="{{ route('auth.loginForm') }}" class="hidden sm:inline text-sm">Log in</a>
                <a href="{{ route('auth.registerForm') }}" class="btn btn-sm btn-primary">Register</a>
            @else
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm normal-case">
                        {{ data_get($currentUser, 'name', 'Account') }}
                    </label>
                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-52 rounded-lg border border-base-300 bg-base-100 p-2 shadow">
                        <li><a href="{{ route('dashboard.index') }}#my-teams">My teams</a></li>
                        <li><a href="{{ route('leagues.joinPrivateForm') }}">Join private league</a></li>
                        <li>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left">Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</header>
