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
        $user = $request->user();

        $tweets = Tweet::with('user')
            ->withCount('likes')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return $tweets->map(function (Tweet $tweet) {
            return [
                'id' => $tweet->id,
                'userName' => $tweet->user ? $tweet->user->name : '',
                'text' => $tweet->text,
                'likeCount' => $tweet->likes_count,
            ];
        });
    }

    /**
     * ツイート詳細 + コメント一覧を返す.
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $tweet = Tweet::with(['user', 'comments.user'])
            ->withCount('likes')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return [
            'post' => [
                'id' => $tweet->id,
                'userName' => $tweet->user ? $tweet->user->name : '',
                'text' => $tweet->text,
                'likeCount' => $tweet->likes_count,
            ],
            'comments' => $tweet->comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'userName' => $comment->user ? $comment->user->name : '',
                    'text' => $comment->text,
                ];
            })->values(),
        ];
    }
}

