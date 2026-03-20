<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Like;
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

        $blockerIds = $user->getBlockerUserIds();

        $tweets = Tweet::with('user')
            ->withCount('likes')
            ->when(count($blockerIds) > 0, function ($query) use ($blockerIds) {
                $query->whereNotIn('user_id', $blockerIds);
            })
            ->latest()
            ->get();

        $likedTweetIds = Like::where('user_id', $user->id)
            ->pluck('tweet_id')
            ->all();

        return $tweets->map(function (Tweet $tweet) use ($likedTweetIds, $user) {
            return [
                'id' => $tweet->id,
                'userName' => $tweet->user ? $tweet->user->name : '',
                'text' => $tweet->text,
                'likeCount' => $tweet->likes_count,
                'createdAt' => $tweet->created_at?->format('Y/m/d H:i'),
                'likedByMe' => in_array($tweet->id, $likedTweetIds, true),
                'canDelete' => $tweet->user_id === $user->id,
            ];
        });
    }

    /**
     * ツイート詳細 + コメント一覧を返す.
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $tweet = Tweet::with('user')
            ->withCount('likes')
            ->findOrFail($id);

        if ($user->isBlockedByUserId($tweet->user_id)) {
            abort(404);
        }

        $likedByMe = Like::where('tweet_id', $tweet->id)
            ->where('user_id', $user->id)
            ->exists();

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
                'likedByMe' => $likedByMe,
            ],
            'comments' => $comments->map(function ($comment) use ($user) {
                return [
                    'id' => $comment->id,
                    'userName' => $comment->user ? $comment->user->name : '',
                    'text' => $comment->text,
                    'createdAt' => $comment->created_at?->format('Y/m/d H:i'),
                    'canDelete' => $comment->user_id === $user->id,
                    'canEdit' => $comment->user_id === $user->id,
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
            'likedByMe' => false,
            'canDelete' => true,
        ], 201);
    }

    /**
     * ログインユーザーのツイート内容を更新する.
     */
    public function update(Request $request, int $id)
    {
        $user = $request->user();

        $tweet = Tweet::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
        ]);

        $tweet->text = $validated['text'];
        $tweet->save();

        $likesCount = $tweet->likes()->count();

        return response()->json([
            'id' => $tweet->id,
            'userName' => $tweet->user ? $tweet->user->name : '',
            'text' => $tweet->text,
            'likeCount' => $likesCount,
            'createdAt' => $tweet->created_at?->format('Y/m/d H:i'),
            'likedByMe' => Like::where('tweet_id', $tweet->id)
                ->where('user_id', $user->id)
                ->exists(),
            'canDelete' => true,
        ]);
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

