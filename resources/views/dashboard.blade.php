@extends('layouts.app')

@section('title', 'Home Feed')

@section('content')

@if (session('success'))
<div class="success-box">{{ session('success') }}</div>
@endif

@if (session('error'))
<div class="error-box">{{ session('error') }}</div>
@endif

@if ($errors->any())
<div class="error-box">
    @foreach ($errors->all() as $error)
    <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="feed-layout">

    {{-- Left Navigation Sidebar --}}
    <aside class="feed-left-sidebar">
        <div class="panel sidebar-panel left-nav-panel">

            <a href="{{ route('dashboard') }}" class="left-nav-brand">
                VYNTRA<span>.</span>
            </a>

            <p class="eyebrow">YOUR SPACE</p>

            <nav class="feed-navigation">
                <a href="{{ route('dashboard') }}" class="feed-nav-link active">
                    <span class="feed-nav-icon">⌂</span>
                    <span>Home Feed</span>
                </a>

                <a href="{{ route('users.show', auth()->user()) }}" class="feed-nav-link">
                    <span class="feed-nav-icon">◎</span>
                    <span>My Profile</span>
                </a>

                <a href="{{ route('profile.edit') }}" class="feed-nav-link">
                    <span class="feed-nav-icon">⚙</span>
                    <span>Edit Profile</span>
                </a>

                <a href="{{ route('notifications.index') }}" class="feed-nav-link">
                    <span class="feed-nav-icon">♧</span>
                    <span>Notifications</span>
                </a>

                <a href="{{ route('users.search') }}" class="feed-nav-link">
                    <span class="feed-nav-icon">⌕</span>
                    <span>Find People</span>
                </a>
            </nav>

            <div class="left-sidebar-note">
                <span class="sidebar-note-dot"></span>
                <p>Your community, your space.</p>
            </div>

        </div>
    </aside>

    {{-- Main Feed --}}
    <section class="feed-main" id="posts-feed">

        {{-- Create Post --}}
        <div class="panel composer">

            <div class="post-avatar">
                @if (auth()->user()->profile_photo)
                <img
                    src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                    alt="{{ auth()->user()->name }}'s profile photo">
                @else
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>

            <div class="composer-content">
                <h2>Create a post</h2>

                <form
                    action="{{ route('posts.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="post-create-form">

                    @csrf

                    <textarea
                        name="content"
                        placeholder="What's on your mind?"
                        maxlength="2000">{{ old('content') }}</textarea>

                    <div class="composer-actions">

                        <label class="upload-label" for="image">
                            <span>＋</span> Add an image
                        </label>

                        <input
                            id="image"
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp">

                        <button class="btn-primary" type="submit">
                            Publish post
                        </button>

                    </div>

                    <p class="muted small-text">
                        Share your thoughts. Images are optional, maximum 2 MB.
                    </p>

                </form>
            </div>
        </div>

        {{-- Feed Heading --}}
        <div class="feed-heading">
            <div>
                <p class="eyebrow">THE COMMUNITY</p>
                <h2>Your Feed</h2>
            </div>

            <span class="feed-total">{{ $posts->total() }} posts</span>
        </div>

        {{-- Posts Feed --}}
        @forelse ($posts as $post)
        <article class="panel post-card" id="post-{{ $post->id }}">

            {{-- Post Header --}}
            <div class="post-header">

                <div class="post-avatar">
                    @if ($post->user->profile_photo)
                    <img
                        src="{{ asset('storage/' . $post->user->profile_photo) }}"
                        alt="{{ $post->user->name }}'s profile photo">
                    @else
                    {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    @endif
                </div>

                <div class="post-author">
                    <strong>
                        <a
                            href="{{ route('users.show', $post->user) }}"
                            class="profile-name-link">
                            {{ $post->user->name }}
                        </a>
                    </strong>

                    <span>{{ $post->created_at->diffForHumans() }}</span>
                </div>

                @if ($post->user_id === auth()->id())
                <form
                    action="{{ route('posts.destroy', $post) }}"
                    method="POST"
                    class="delete-post-form">

                    @csrf
                    @method('DELETE')

                    <button class="delete-btn" type="submit">
                        Delete
                    </button>
                </form>
                @endif

            </div>

            {{-- Post Content --}}
            @if ($post->content)
            <p class="post-content">{{ $post->content }}</p>
            @endif

            {{-- Post Image --}}
            @if ($post->image)
            <div class="post-image-wrap">
                <img
                    class="post-image"
                    src="{{ asset('storage/' . $post->image) }}"
                    alt="Image attached to post"
                    loading="lazy">
            </div>
            @endif

            {{-- Likes and Comment Count --}}
            <div class="post-stats">

                <form
                    action="{{ route('posts.like', $post) }}"
                    method="POST"
                    class="like-form">

                    @csrf

                    <button
                        type="submit"
                        class="like-btn {{ in_array($post->id, $likedPostIds) ? 'liked' : '' }}"
                        aria-pressed="{{ in_array($post->id, $likedPostIds) ? 'true' : 'false' }}">

                        <span class="like-label">
                            {{ in_array($post->id, $likedPostIds) ? '♥ Liked' : '♡ Like' }}
                        </span>

                        · <span class="like-count">{{ $post->likes_count }}</span>
                    </button>
                </form>

                <a
                    class="comment-count"
                    href="#comments-{{ $post->id }}">

                    💬 {{ $post->comments_count }}
                    {{ $post->comments_count === 1 ? 'comment' : 'comments' }}
                </a>

            </div>

            {{-- Comments Section --}}
            <div
                class="comments-section"
                id="comments-{{ $post->id }}">

                <h3>
                    Conversation
                    <span>{{ $post->comments_count }}</span>
                </h3>

                <div class="comments-list">

                    @forelse ($post->comments as $comment)
                    <div
                        class="comment-item"
                        id="comment-{{ $comment->id }}">

                        {{-- Comment Author Avatar --}}
                        <div class="comment-avatar">
                            @if ($comment->user->profile_photo)
                            <img
                                src="{{ asset('storage/' . $comment->user->profile_photo) }}"
                                alt="{{ $comment->user->name }}'s profile photo">
                            @else
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                            @endif
                        </div>

                        <div class="comment-content">

                            <div class="comment-body">
                                <div class="comment-heading">
                                    <strong>{{ $comment->user->name }}</strong>

                                    <span>
                                        {{ $comment->created_at->diffForHumans() }}
                                    </span>

                                    @if ($comment->created_at->ne($comment->updated_at))
                                    <span class="edited-label">Edited</span>
                                    @endif
                                </div>

                                <p>{{ $comment->content }}</p>
                            </div>

                            {{-- Edit and Delete Own Comments --}}
                            @if ($comment->user_id === auth()->id())
                            <div class="comment-actions">

                                <details class="comment-edit">
                                    <summary>Edit</summary>

                                    <form
                                        action="{{ route('comments.update', $comment) }}"
                                        method="POST"
                                        class="edit-comment-form">

                                        @csrf
                                        @method('PUT')

                                        <textarea
                                            name="content"
                                            maxlength="1000"
                                            required>{{ $comment->content }}</textarea>

                                        <div class="edit-actions">
                                            <button
                                                class="btn-primary"
                                                type="submit">
                                                Save changes
                                            </button>

                                            <button
                                                class="cancel-btn"
                                                type="button"
                                                onclick="this.closest('details').open = false">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </details>

                                <form
                                    action="{{ route('comments.destroy', $comment) }}"
                                    method="POST"
                                    class="delete-comment-form">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-delete">
                                        Delete
                                    </button>
                                </form>

                            </div>
                            @endif

                        </div>
                    </div>

                    @empty
                    <p class="empty-comments">
                        No comments yet. Be the first to start the conversation.
                    </p>
                    @endforelse

                </div>

                {{-- Add a Comment --}}
                <form
                    action="{{ route('comments.store', $post) }}"
                    method="POST"
                    class="comment-form">

                    @csrf

                    <div class="comment-avatar current-avatar">
                        @if (auth()->user()->profile_photo)
                        <img
                            src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                            alt="Your profile photo">
                        @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>

                    <input
                        type="text"
                        name="content"
                        placeholder="Write a comment..."
                        maxlength="1000"
                        required>

                    <button class="btn-primary" type="submit">
                        Send
                    </button>
                </form>

            </div>
        </article>

        @empty
        <div class="panel empty-feed">
            <div class="empty-icon">✦</div>
            <h2>Your feed starts here.</h2>
            <p class="muted">
                Publish your first post and make Vyntra yours.
            </p>
        </div>
        @endforelse

        {{-- Pagination --}}
        <div class="pagination">
            {{ $posts->links() }}
        </div>

    </section>

    {{-- Right Sidebar --}}
    <aside class="feed-sidebar">

        {{-- Profile Links --}}
        <div class="panel sidebar-panel">
            <p class="eyebrow">YOUR VYNTRA</p>
            <h2>Welcome aboard.</h2>

            <p class="muted">
                Your space to share thoughts, moments, and ideas with your growing community.
            </p>

            <div class="profile-sidebar-links">
                <a
                    href="{{ route('users.show', auth()->user()) }}"
                    class="btn-primary">
                    My Profile
                </a>

                <a
                    href="{{ route('profile.edit') }}"
                    class="btn-secondary">
                    Edit Profile
                </a>
            </div>
        </div>

        {{-- Discover People --}}
        <div class="panel sidebar-panel users-section">
            <p class="eyebrow">GROW YOUR COMMUNITY</p>
            <h2>Discover People</h2>

            <p class="muted small-text">
                Find people and connect with your community.
            </p>

            <div class="discover-summary">
                <span>People to discover</span>
                <strong>{{ $users->count() }}</strong>
            </div>

            @forelse ($users as $user)
            <div class="user-card">

                <div class="post-avatar user-avatar">
                    @if ($user->profile_photo)
                    <img
                        src="{{ asset('storage/' . $user->profile_photo) }}"
                        alt="{{ $user->name }}'s profile photo">
                    @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>

                <div class="user-info">
                    <strong>
                        <a
                            href="{{ route('users.show', $user) }}"
                            class="profile-name-link">
                            {{ $user->name }}
                        </a>
                    </strong>

                    <p class="follower-count">
                        {{ $user->followers_count }}
                        {{ $user->followers_count === 1 ? 'follower' : 'followers' }}
                    </p>
                </div>

                <form
                    action="{{ route('users.follow', $user) }}"
                    method="POST">

                    @csrf

                    <button
                        type="submit"
                        class="btn-primary follow-btn">
                        {{ in_array($user->id, $followingIds) ? 'Unfollow' : 'Follow' }}
                    </button>
                </form>

            </div>

            @empty
            <p class="muted small-text">
                No other users yet. Invite someone to join Vyntra!
            </p>
            @endforelse

        </div>

        {{-- Community Guidelines --}}
        <div class="panel sidebar-panel">
            <h3>Community guidelines</h3>

            <p class="muted small-text">
                Be respectful, share responsibly, and help keep Vyntra welcoming for everyone.
            </p>
        </div>

    </aside>

</div>

@endsection