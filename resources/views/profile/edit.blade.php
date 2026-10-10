@extends('layouts.app')

@section('content')
<div class="edit-profile-page">

    <div class="edit-profile-card">
        <h1>Edit Profile</h1>
        <p class="muted-text">
            Update your profile information and picture.
        </p>

        @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form
            action="{{ route('profile.update') }}"
            method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Current Profile Picture</label>

                @if ($user->profile_photo)
                <img
                    src="{{ asset('storage/' . $user->profile_photo) }}"
                    alt="Current profile picture"
                    class="edit-profile-avatar">
                @else
                <div class="profile-avatar profile-avatar-fallback">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                @endif
            </div>

            <div class="form-group">
                <label for="profile_photo">Upload New Picture</label>
                <input
                    type="file"
                    id="profile_photo"
                    name="profile_photo"
                    accept=".jpg,.jpeg,.png,.webp">
                <small>JPG, PNG, or WebP. Maximum size: 2 MB.</small>
            </div>

            <div class="form-group">
                <label for="name">Display Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    maxlength="255"
                    required>
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea
                    id="bio"
                    name="bio"
                    rows="5"
                    maxlength="500"
                    placeholder="Tell people a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                <small>Maximum 500 characters.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Save Changes
                </button>

                <a
                    href="{{ route('users.show', $user) }}"
                    class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection