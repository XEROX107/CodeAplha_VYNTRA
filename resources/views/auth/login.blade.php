@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-card">
    <p class="eyebrow">WELCOME BACK</p>
    <h1>Your world, <span>reconnected.</span></h1>
    <p class="muted">Log in to continue your Vyntra experience.</p>

    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf

        <label for="email">Email address</label>
        <input id="email" type="email" name="email"
            value="{{ old('email') }}" required autocomplete="email">

        <label for="password">Password</label>
        <input id="password" type="password" name="password"
            required autocomplete="current-password">

        <label class="remember-label">
            <input type="checkbox" name="remember" value="1">
            Remember me
        </label>

        <button class="btn-primary full-width" type="submit">Log In</button>
    </form>

    <p class="auth-footer">
        New to Vyntra? <a href="{{ route('register') }}">Create an account</a>
    </p>
</div>
@endsection