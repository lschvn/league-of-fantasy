@extends('layouts.app')

@section('title', 'Join private league · League of Fantasy')

@section('content')
    <section class="max-w-sm space-y-6">
        <div class="space-y-2">
            <h1 class="text-xl font-semibold">Join private league</h1>
        </div>

        <form method="POST" action="{{ route('leagues.joinPrivate') }}" class="space-y-3">
            @csrf

            <div class="form-control">
                <label for="code" class="label">
                    <span class="label-text">Code</span>
                </label>
                <input id="code" type="text" name="code" value="{{ old('code') }}" class="input input-bordered w-full" required>
                @error('code')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control">
                <label for="team_name" class="label">
                    <span class="label-text">Team name</span>
                </label>
                <input id="team_name" type="text" name="team_name" value="{{ old('team_name') }}" class="input input-bordered w-full">
                @error('team_name')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Join</button>
        </form>
    </section>
@endsection
