<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Tweet;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * ツイートにいいねする.
     */
    public function store(Request $request, int $id)
    {
        $user = $request->user();

        $tweet = Tweet::findOrFail($id);

        Like::firstOrCreate([
            'tweet_id' => $tweet->id,
            'user_id' => $user->id,
        ]);

        $likeCount = $tweet->likes()->count();

        return response()->json([
            'likeCount' => $likeCount,
            'likedByMe' => true,
        ]);
    }

    /**
     * ツイートのいいねを外す.
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();

        $tweet = Tweet::findOrFail($id);

        Like::where('tweet_id', $tweet->id)
            ->where('user_id', $user->id)
            ->delete();

        $likeCount = $tweet->likes()->count();

        return response()->json([
            'likeCount' => $likeCount,
            'likedByMe' => false,
        ]);
    }
}

