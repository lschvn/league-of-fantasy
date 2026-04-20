@extends('layouts.app')

@section('title', '500 · League of Fantasy')

@section('content')
    <section class="max-w-lg space-y-3">
        <h1 class="text-xl font-semibold">500</h1>
        <p class="text-sm text-base-content/70">Something went wrong while rendering this page.</p>
        <a href="{{ route('landing') }}" class="link link-hover">Back to home</a>
    </section>
@endsection
