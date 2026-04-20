@extends('layouts.app')

@section('title', 'Log in · League of Fantasy')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center">
        <section class="w-full max-w-sm space-y-6">
            <div class="space-y-2">
                <h1 class="text-xl font-semibold">Log in</h1>
            </div>

            <form method="POST" action="{{ route('auth.login') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="device_name" value="web">

                <div class="form-control">
                    <label for="email" class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full" autocomplete="email" required>
                    @error('email')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="password" class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <input id="password" type="password" name="password" class="input input-bordered w-full" autocomplete="current-password" required>
                    @error('password')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">Log in</button>
            </form>

            <p class="text-sm text-base-content/70">
                Don't have an account?
                <a href="{{ route('auth.registerForm') }}" class="link link-hover">Register</a>
            </p>
        </section>
    </div>
@endsection
