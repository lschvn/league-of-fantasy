@extends('layouts.app')

@section('title', '419 · League of Fantasy')

@section('content')
    <section class="max-w-lg space-y-3">
        <h1 class="text-xl font-semibold">419</h1>
        <p class="text-sm text-base-content/70">Your session expired before the request could be completed.</p>
        <a href="{{ route('landing') }}" class="link link-hover">Back to home</a>
    </section>
@endsection
