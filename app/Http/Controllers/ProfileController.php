<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $user->loadCount(['posts', 'followers', 'following']);

        $posts = $user->posts()
            ->with('user')
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(9);

        return view('profile.show', compact('user', 'posts'));
    }

    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($user->id),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $user->name = $validated['name'];
        $user->bio = $validated['bio'] ?? null;

        if ($request->hasFile('profile_photo')) {
            $oldPhoto = $user->profile_photo;

            $newPhoto = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            $user->profile_photo = $newPhoto;

            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        $user->save();

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Your profile was updated successfully!');
    }

    public function followers(User $user)
    {
        $connections = $user->followers()
            ->orderBy('name')
            ->paginate(20);

        $connectionType = 'Followers';

        return view('profile.connections', compact(
            'user',
            'connections',
            'connectionType'
        ));
    }

    public function following(User $user)
    {
        $connections = $user->following()
            ->orderBy('name')
            ->paginate(20);

        $connectionType = 'Following';

        return view('profile.connections', compact(
            'user',
            'connections',
            'connectionType'
        ));
    }
}