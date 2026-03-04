<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tweet;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    /**
     * ホームタイムライン用のツイート一覧を返す.
     */
    public function index(Request $request)
    {
        $tweets = Tweet::with('user')
            ->withCount('likes')
            ->latest()
            ->get();

        return $tweets->map(function (Tweet $tweet) {
            return [
                'id' => $tweet->id,
                'userName' => $tweet->user ? $tweet->user->name : '',
                'text' => $tweet->text,
                'likeCount' => $tweet->likes_count,
                'createdAt' => $tweet->created_at?->format('Y/m/d H:i'),
            ];
        });
    }

    /**
     * ツイート詳細 + コメント一覧を返す.
     */
    public function show(Request $request, int $id)
    {
        $tweet = Tweet::with('user')
            ->withCount('likes')
            ->findOrFail($id);

        // コメントは新しい順で取得
        $comments = $tweet->comments()
            ->with('user')
            ->latest()
            ->get();

        return [
            'post' => [
                'id' => $tweet->id,
                'userName' => $tweet->user ? $tweet->user->name : '',
                'text' => $tweet->text,
                'likeCount' => $tweet->likes_count,
                'createdAt' => $tweet->created_at?->format('Y/m/d H:i'),
            ],
            'comments' => $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'userName' => $comment->user ? $comment->user->name : '',
                    'text' => $comment->text,
                    'createdAt' => $comment->created_at?->format('Y/m/d H:i'),
                ];
            })->values(),
        ];
    }

    /**
     * 新しいツイートを作成する.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
        ]);

        $tweet = Tweet::create([
            'user_id' => $user->id,
            'text' => $validated['text'],
        ]);

        $tweet->load('user');

        return response()->json([
            'id' => $tweet->id,
            'userName' => $tweet->user ? $tweet->user->name : '',
            'text' => $tweet->text,
            'likeCount' => 0,
            'createdAt' => $tweet->created_at?->format('Y/m/d H:i'),
        ], 201);
    }

    /**
     * ログインユーザーのツイートを削除する.
     * 関連するコメント・いいねは外部キーの onDelete('cascade') で同時に削除される.
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();

        $tweet = Tweet::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $tweet->delete();

        return response()->noContent();
    }
}

