@extends('layouts.app')

@section('title', 'Search People')

@section('content')
<div class="panel search-results-page">
    <h1>Discover People</h1>
    <p>Find people and connect with them on Vyntra.</p>

    <form action="{{ route('users.search') }}" method="GET" class="search-page-form">
        <input
            type="search"
            name="q"
            value="{{ $search }}"
            placeholder="Search by name..."
            maxlength="100"
            required>
        <button type="submit" class="btn-primary">Search</button>
    </form>

    @if ($search !== '')
    <h2>Results for "{{ $search }}"</h2>

    @forelse ($users as $user)
    <div class="search-user-card">
        <div class="search-user-info">
            @if ($user->profile_photo)
            <img
                src="{{ asset('storage/' . $user->profile_photo) }}"
                alt="{{ $user->name }}"
                class="avatar">
            @else
            <div class="avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            @endif

            <div>
                <a href="{{ route('users.show', $user) }}">
                    <strong>{{ $user->name }}</strong>
                </a>

                <p>Vyntra member</p>
            </div>
        </div>

        <a href="{{ route('users.show', $user) }}" class="btn-primary">
            View Profile
        </a>
    </div>
    @empty
    <p>No users found matching "{{ $search }}". Try another name.</p>
    @endforelse

    <div class="pagination-container">
        {{ $users->links() }}
    </div>
    @else
    <p>Enter a name above to find Vyntra members.</p>
    @endif
</div>
@endsection