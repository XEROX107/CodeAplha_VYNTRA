<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Like;

class DashboardController extends Controller
{
    public function index()
    {
        // Load posts with their authors and comments
        $posts = Post::with(['user', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(10);

        // Find posts liked by the current user
        $likedPostIds = Like::where('user_id', auth()->id())
            ->whereIn('post_id', $posts->getCollection()->pluck('id'))
            ->pluck('post_id')
            ->all();

        // Load other users and their follower counts
        $users = User::where('id', '!=', auth()->id())
            ->withCount('followers')
            ->orderBy('name')
            ->get();

        // Find the users followed by the current user
        $followingIds = auth()->user()
            ->following()
            ->pluck('users.id')
            ->all();

        return view('dashboard', compact(
            'posts',
            'likedPostIds',
            'users',
            'followingIds'
        ));
    }
}
