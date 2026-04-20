@extends('layouts.app')

@section('title', 'Create league · League of Fantasy')

@section('content')
    <section class="max-w-lg space-y-6">
        <div class="space-y-2">
            <h1 class="text-xl font-semibold">Create league</h1>
        </div>

        <form method="POST" action="{{ route('leagues.store') }}" class="space-y-3">
            @csrf

            <div class="form-control">
                <label for="competition_id" class="label">
                    <span class="label-text">Competition</span>
                </label>
                <select id="competition_id" name="competition_id" class="select select-bordered w-full" required>
                    <option value="">Select a competition</option>
                    @foreach ($competitions as $competition)
                        <option value="{{ $competition['id'] }}" @selected(old('competition_id') == $competition['id'])>
                            {{ $competition['name'] }} · {{ $competition['season'] }}
                        </option>
                    @endforeach
                </select>
                @error('competition_id')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control">
                <label for="name" class="label">
                    <span class="label-text">Name</span>
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full" required>
                @error('name')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control space-y-2">
                <span class="label-text">Visibility</span>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="visibility" value="public" class="radio radio-sm" @checked(old('visibility', 'public') === 'public')>
                        <span>Public</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="visibility" value="private" class="radio radio-sm" @checked(old('visibility') === 'private')>
                        <span>Private</span>
                    </label>
                </div>
                @error('visibility')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control">
                <label for="max_participants" class="label">
                    <span class="label-text">Max participants</span>
                </label>
                <input id="max_participants" type="number" name="max_participants" value="{{ old('max_participants', 8) }}" min="2" max="64" class="input input-bordered w-full" required>
                @error('max_participants')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control">
                <label for="budget_cap" class="label">
                    <span class="label-text">Budget cap</span>
                </label>
                <input id="budget_cap" type="number" name="budget_cap" value="{{ old('budget_cap', 100) }}" min="1" step="1" class="input input-bordered w-full" required>
                @error('budget_cap')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control">
                <label for="join_deadline" class="label">
                    <span class="label-text">Join deadline</span>
                </label>
                <input id="join_deadline" type="datetime-local" name="join_deadline" value="{{ old('join_deadline') }}" class="input input-bordered w-full" required>
                @error('join_deadline')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control">
                <label for="scoring_rule_version" class="label">
                    <span class="label-text">Scoring rule version</span>
                </label>
                <input id="scoring_rule_version" type="text" name="scoring_rule_version" value="{{ old('scoring_rule_version') }}" class="input input-bordered w-full">
                @error('scoring_rule_version')
                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="btn btn-primary">Create league</button>
                <a href="{{ route('leagues.index') }}" class="text-sm hover:text-primary transition-colors">Cancel</a>
            </div>
        </form>
    </section>
@endsection
