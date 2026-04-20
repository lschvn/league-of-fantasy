@extends('layouts.app')

@section('title', '404 · League of Fantasy')

@section('content')
    <section class="max-w-lg space-y-3">
        <h1 class="text-xl font-semibold">404</h1>
        <p class="text-sm text-base-content/70">The page you requested could not be found.</p>
        <a href="{{ route('landing') }}" class="link link-hover">Back to home</a>
    </section>
@endsection
