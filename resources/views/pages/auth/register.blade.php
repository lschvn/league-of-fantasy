@extends('layouts.app')

@section('title', 'Register · League of Fantasy')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center">
        <section class="w-full max-w-sm space-y-6">
            <div class="space-y-2">
                <h1 class="text-xl font-semibold">Register</h1>
            </div>

            <form method="POST" action="{{ route('auth.register') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="device_name" value="web">

                <div class="form-control">
                    <label for="name" class="label">
                        <span class="label-text">Name</span>
                    </label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" class="input input-bordered w-full" autocomplete="name" required>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

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
                    <input id="password" type="password" name="password" class="input input-bordered w-full" autocomplete="new-password" required>
                    @error('password')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="password_confirmation" class="label">
                        <span class="label-text">Confirm password</span>
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input input-bordered w-full" autocomplete="new-password" required>
                    @error('password_confirmation')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">Register</button>
            </form>

            <p class="text-sm text-base-content/70">
                Already have an account?
                <a href="{{ route('auth.loginForm') }}" class="link link-hover">Log in</a>
            </p>
        </section>
    </div>
@endsection
