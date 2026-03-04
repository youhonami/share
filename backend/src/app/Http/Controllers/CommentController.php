<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Tweet;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * 指定ツイートにコメントを追加する.
     */
    public function store(Request $request, int $id)
    {
        $user = $request->user();

        $tweet = Tweet::findOrFail($id);

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
        ]);

        $comment = Comment::create([
            'tweet_id' => $tweet->id,
            'user_id' => $user->id,
            'text' => $validated['text'],
        ]);

        $comment->load('user');

        return response()->json([
            'id' => $comment->id,
            'userName' => $comment->user ? $comment->user->name : '',
            'text' => $comment->text,
            'createdAt' => $comment->created_at?->format('Y/m/d H:i'),
        ], 201);
    }
}

