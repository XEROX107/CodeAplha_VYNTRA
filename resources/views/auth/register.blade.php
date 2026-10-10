@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="auth-card">
    <p class="eyebrow">YOUR PEOPLE. YOUR SPACE.</p>
    <h1>Join the <span>conversation.</span></h1>
    <p class="muted">Create your Vyntra account and connect with people.</p>

    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
        @csrf

        <label for="name">Full name</label>
        <input id="name" type="text" name="name"
            value="{{ old('name') }}" required autocomplete="name">

        <label for="email">Email address</label>
        <input id="email" type="email" name="email"
            value="{{ old('email') }}" required autocomplete="email">

        <label for="password">Password</label>
        <input id="password" type="password" name="password"
            required autocomplete="new-password">

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password"
            name="password_confirmation" required autocomplete="new-password">

        <button class="btn-primary full-width" type="submit">Create Account</button>
    </form>

    <p class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </p>
</div>
@endsection