@extends('layouts.app')

@section('title', $team['name'].' lineup · League of Fantasy')

@section('content')
    <section class="space-y-2">
        <h1 class="text-xl font-semibold">{{ $team['name'] }} lineup</h1>
        <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/70">
            <span>Lineup locks on @uiDate($week['lineup_lock_at']). Submit exactly 7 players.</span>
            @if ($isLocked)
                <span class="badge badge-neutral">Locked</span>
            @endif
        </div>
    </section>

    <form method="POST" action="{{ route('lineups.store', $team['id']) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="week_id" value="{{ $week['id'] }}">

        @error('slots')
            <p class="text-error text-sm">{{ $message }}</p>
        @enderror

        <div class="overflow-x-auto border border-base-300 rounded-lg">
            <table class="table">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Player</th>
                        <th class="text-right">Captain</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($positions as $index => $position)
                        @php
                            $selectedValue = old("slots.$position.roster_slot_id", $selectedSlots[$position] ?? '');
                            $matchingSlots = collect($roster)->filter(fn (array $slot) => $position === 'FLEX_1' || $position === 'FLEX_2' || data_get($slot, 'player.role') === $position);
                            $otherSlots = collect($roster)->reject(fn (array $slot) => $matchingSlots->contains('id', $slot['id']));
                            $captainValue = old('captain', $captainPosition);
                            $slotErrorKey = "slots.$index.roster_slot_id";
                        @endphp
                        <tr>
                            <td class="font-medium font-mono text-xs uppercase">{{ $position }}</td>
                            <td class="min-w-[240px]">
                                <select name="slots[{{ $position }}][roster_slot_id]" class="select select-bordered select-sm w-full" @disabled($isLocked)>
                                    <option value="">Select a player</option>
                                    @foreach ($matchingSlots as $slot)
                                        <option value="{{ $slot['id'] }}" @selected((int) $selectedValue === (int) $slot['id'])>
                                            {{ data_get($slot, 'player.nickname') }} · {{ data_get($slot, 'player.role') }}
                                        </option>
                                    @endforeach
                                    @if (($position !== 'FLEX_1' && $position !== 'FLEX_2') && $otherSlots->isNotEmpty())
                                        <optgroup label="Other active players">
                                            @foreach ($otherSlots as $slot)
                                                <option value="{{ $slot['id'] }}" @selected((int) $selectedValue === (int) $slot['id'])>
                                                    {{ data_get($slot, 'player.nickname') }} · {{ data_get($slot, 'player.role') }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                @error($slotErrorKey)
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="text-right">
                                <input type="radio" name="captain" value="{{ $position }}" class="radio radio-sm" @checked($captainValue === $position) @disabled($isLocked)>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-4">
            @if (! $isLocked)
                <button type="submit" class="btn btn-primary">Submit lineup</button>
            @else
                <span class="badge badge-neutral">This lineup is locked</span>
            @endif
            <a href="{{ route('teams.show', $team['id']) }}" class="text-sm hover:text-primary transition-colors">Back to team</a>
        </div>
    </form>
@endsection
