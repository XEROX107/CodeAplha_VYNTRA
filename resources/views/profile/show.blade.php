@extends('layouts.app')

@section('title', $user->name . "'s Profile")

@section('content')
<div class="profile-page">

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <section class="profile-card">
        <div class="profile-header">

            @if ($user->profile_photo)
            <img
                src="{{ asset('storage/' . $user->profile_photo) }}"
                alt="{{ $user->name }}"
                class="profile-avatar">
            @else
            <div class="profile-avatar profile-avatar-fallback">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            @endif

            <div class="profile-details">
                <h1>{{ $user->name }}</h1>

                <p class="profile-bio">
                    {{ $user->bio ?: 'This user has not added a bio yet.' }}
                </p>

                <div class="profile-stats">
                    <span>
                        <strong>{{ $user->posts_count }}</strong> Posts
                    </span>

                    <a href="{{ route('users.followers', $user) }}">
                        <strong>{{ $user->followers_count }}</strong> Followers
                    </a>

                    <a href="{{ route('users.following', $user) }}">
                        <strong>{{ $user->following_count }}</strong> Following
                    </a>
                </div>

                @if (auth()->id() === $user->id)

                <a href="{{ route('profile.edit') }}"
                    class="btn-primary">
                    Edit Profile
                </a>

                @else

                @php
                $isFollowing = auth()->user()
                ->following()
                ->where('users.id', $user->id)
                ->exists();
                @endphp

                <form
                    method="POST"
                    action="{{ route('users.follow', $user) }}">
                    @csrf

                    <button
                        type="submit"
                        class="{{ $isFollowing ? 'btn-secondary' : 'btn-primary' }}">
                        {{ $isFollowing ? 'Unfollow' : 'Follow' }}
                    </button>
                </form>

                @endif
            </div>
        </div>
    </section>

    <section class="profile-posts">
        <h2>Posts by {{ $user->name }}</h2>

        @forelse ($posts as $post)
        <article class="post-card">

            <div class="post-header">
                <a href="{{ route('users.show', $post->user) }}">
                    {{ $post->user->name }}
                </a>

                <span class="post-time">
                    {{ $post->created_at->diffForHumans() }}
                </span>
            </div>

            @if ($post->content)
            <p class="post-content">
                {{ $post->content }}
            </p>
            @endif

            @php
            $imagePath = $post->image_path
            ?? $post->image
            ?? null;
            @endphp

            @if ($imagePath)
            <div class="post-image-wrap">
                <img
                    src="{{ asset('storage/' . $imagePath) }}"
                    alt="Post image"
                    class="post-image">
            </div>
            @endif

            <div class="post-meta">
                <span>{{ $post->likes_count }} Likes</span>
                <span>{{ $post->comments_count }} Comments</span>
            </div>

        </article>
        @empty
        <div class="empty-state">
            <h3>No posts yet</h3>
            <p>This user hasn't shared any posts.</p>
        </div>
        @endforelse

        <div class="pagination-wrapper">
            {{ $posts->links() }}
        </div>
    </section>

</div>
@endsection