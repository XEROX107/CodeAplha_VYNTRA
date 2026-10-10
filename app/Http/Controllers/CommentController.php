<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Notification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        $comment = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        // Notify the post owner, except when commenting on your own post
        if ((int) $post->user_id !== (int) $user->id) {
            Notification::create([
                'user_id' => $post->user_id,
                'actor_id' => $user->id,
                'post_id' => $post->id,
                'type' => 'comment',
                'message' => $user->name . ' commented on your post.',
                'is_read' => false,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Comment added successfully!',
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_name' => $comment->user->name,
                    'profile_photo' => $comment->user->profile_photo,
                    'created_at' => $comment->created_at->diffForHumans(),
                ],
                'comments_count' => $post->comments()->count(),
            ], 201);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Comment added successfully!');
    }

    public function update(Request $request, Comment $comment)
    {
        abort_unless(
            (int) $comment->user_id === (int) $request->user()->id,
            403
        );

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment->update([
            'content' => $validated['content'],
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Comment updated successfully!');
    }

    public function destroy(Request $request, Comment $comment)
    {
        abort_unless(
            (int) $comment->user_id === (int) $request->user()->id,
            403
        );

        $comment->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Comment deleted successfully!');
    }
}
