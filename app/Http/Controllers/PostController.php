<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = $request->user()->posts()->create([
            'content' => $validated['content'],
            'image' => $imagePath,
        ]);

        if ($request->expectsJson()) {
            $post->load('user')->loadCount(['likes', 'comments']);

            return response()->json([
                'message' => 'Your post has been published!',
                'post' => [
                    'id' => $post->id,
                    'content' => $post->content,
                    'image' => $post->image
                        ? asset('storage/' . $post->image)
                        : null,
                    'user_name' => $post->user->name,
                    'profile_photo' => $post->user->profile_photo,
                    'created_at' => $post->created_at->diffForHumans(),
                    'likes_count' => $post->likes_count,
                    'comments_count' => $post->comments_count,
                ],
            ], 201);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your post has been published!');
    }
    public function destroy(Post $post)
    {
        abort_unless($post->user_id === auth()->id(), 403);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your post has been deleted.');
    }
}
