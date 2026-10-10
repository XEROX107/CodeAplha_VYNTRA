@extends('layouts.app')

@section('content')
<div class="connections-page">

    <div class="connections-card">
        <h1>{{ $connectionType }}</h1>

        <p class="muted-text">
            {{ $connectionType }} of {{ $user->name }}
        </p>

        @forelse ($connections as $person)
        <div class="connection-item">

            <a
                href="{{ route('users.show', $person) }}"
                class="connection-profile">
                @if ($person->profile_photo)
                <img
                    src="{{ asset('storage/' . $person->profile_photo) }}"
                    alt="{{ $person->name }}"
                    class="connection-avatar">
                @else
                <div class="connection-avatar profile-avatar-fallback">
                    {{ strtoupper(substr($person->name, 0, 1)) }}
                </div>
                @endif

                <span>{{ $person->name }}</span>
            </a>

            @if (auth()->id() === $person->id)
            <span class="muted-text">You</span>
            @endif

        </div>
        @empty
        <div class="empty-state">
            <p>No {{ strtolower($connectionType) }} to display yet.</p>
        </div>
        @endforelse

        <div class="pagination-wrapper">
            {{ $connections->links() }}
        </div>

        <a href="{{ route('users.show', $user) }}"
            class="btn-secondary">
            Back to Profile
        </a>
    </div>

</div>
@endsection