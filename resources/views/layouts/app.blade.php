<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Vyntra') | Vyntra</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <header class="topbar">
        <div class="nav-inner">

            <a href="{{ route('dashboard') }}" class="brand">
                Vyntra<span>.</span>
            </a>

            @auth
            <form action="{{ route('users.search') }}"
                method="GET"
                class="nav-search-form">

                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search people..."
                    aria-label="Search people"
                    required>

                <button type="submit" class="search-btn">
                    Search
                </button>
            </form>

            <nav class="nav-user" aria-label="Main navigation">

                <a href="{{ route('dashboard') }}" class="nav-link">
                    Feed
                </a>

                <a href="{{ route('notifications.index') }}"
                    class="nav-link notification-link"
                    aria-label="Notifications">

                    <span aria-hidden="true">♧</span>
                    Notifications

                    @php
                    $unreadCount = auth()->user()
                    ->notifications()
                    ->where('is_read', false)
                    ->count();
                    @endphp

                    @if ($unreadCount > 0)
                    <span class="notification-badge">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                    @endif
                </a>

                {{-- User Profile with Uploaded Photo --}}
                <a href="{{ route('users.show', auth()->user()) }}"
                    class="nav-link profile-nav-link">

                    <span class="nav-avatar">
                        @if (auth()->user()->profile_photo)
                        <img
                            src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                            alt="{{ auth()->user()->name }}'s profile picture">
                        @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </span>

                    <span class="nav-profile-name">
                        {{ auth()->user()->name }}
                    </span>
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button class="logout-btn" type="submit">
                        Logout
                    </button>
                </form>

            </nav>
            @else
            <nav class="nav-user" aria-label="Guest navigation">

                <a href="{{ route('login') }}" class="nav-link">
                    Login
                </a>

                <a href="{{ route('register') }}" class="btn-primary">
                    Join Vyntra
                </a>

            </nav>
            @endauth

        </div>
    </header>

    <main class="page-container">

        @if ($errors->any())
        <div class="error-box">
            @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        @if (session('success'))
        <div class="success-box">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="error-box">
            {{ session('error') }}
        </div>
        @endif

        @yield('content')

    </main>
</body>

</html>