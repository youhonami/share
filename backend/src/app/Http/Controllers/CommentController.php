<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * 指定ツイートにコメントを追加する.
     */
    public function store(Request $request, int $id)
    {
        $user = $request->user();

        $tweet = Tweet::findOrFail($id);

        if ($user->isBlockedByUserId($tweet->user_id)) {
            abort(404);
        }

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
            'canDelete' => true,
            'canEdit' => true,
        ], 201);
    }

    /**
     * ログインユーザーのコメント内容を更新する.
     */
    public function update(Request $request, int $id)
    {
        $user = $request->user();

        $comment = Comment::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $comment->load('tweet');
        if ($comment->tweet && $user->isBlockedByUserId($comment->tweet->user_id)) {
            abort(404);
        }

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
        ]);

        $comment->text = $validated['text'];
        $comment->save();

        return response()->json([
            'id' => $comment->id,
            'userName' => $comment->user ? $comment->user->name : '',
            'text' => $comment->text,
            'createdAt' => $comment->created_at?->format('Y/m/d H:i'),
            'canDelete' => true,
            'canEdit' => true,
        ]);
    }

    /**
     * ログインユーザーのコメントを削除する.
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();

        $comment = Comment::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $comment->load('tweet');
        if ($comment->tweet && $user->isBlockedByUserId($comment->tweet->user_id)) {
            abort(404);
        }

        $comment->delete();

        return response()->noContent();
    }
}

