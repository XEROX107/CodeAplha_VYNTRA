<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function toggle(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ((int) $currentUser->id === (int) $user->id) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        $isFollowing = $currentUser->following()
            ->where('users.id', $user->id)
            ->exists();

        if ($isFollowing) {
            // Unfollow the user
            $currentUser->following()->detach($user->id);

            return back()->with('success', 'Unfollowed successfully!');
        }

        // Follow the user
        $currentUser->following()->attach($user->id);

        // Notify the user who received the follow
        Notification::create([
            'user_id' => $user->id,
            'actor_id' => $currentUser->id,
            'post_id' => null,
            'type' => 'follow',
            'message' => $currentUser->name . ' started following you.',
            'is_read' => false,
        ]);

        return back()->with('success', 'Followed successfully!');
    }
}
