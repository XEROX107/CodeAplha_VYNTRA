<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Notification;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $user = $request->user();

        $like = Like::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;

            // Remove the corresponding like notification
            Notification::where('user_id', $post->user_id)
                ->where('actor_id', $user->id)
                ->where('post_id', $post->id)
                ->where('type', 'like')
                ->delete();
        } else {
            Like::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);

            $liked = true;

            // Notify the post owner, but not when liking your own post
            if ($post->user_id !== $user->id) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'actor_id' => $user->id,
                    'post_id' => $post->id,
                    'type' => 'like',
                    'message' => $user->name . ' liked your post.',
                    'is_read' => false,
                ]);
            }
        }

        $likesCount = Like::where('post_id', $post->id)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $liked ? 'Post liked!' : 'Like removed!',
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
        }

        return redirect()->route('dashboard');
    }
}
