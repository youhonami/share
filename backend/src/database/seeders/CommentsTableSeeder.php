<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Tweet;
use App\Models\User;

class CommentsTableSeeder extends Seeder
{
    public function run()
    {
        $tweets = Tweet::all();
        $users  = User::all();

        if ($tweets->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($tweets as $tweet) {
            // 各ツイートに 2 件ずつコメントを付ける
            for ($i = 1; $i <= 2; $i++) {
                $user = $users->random();

                Comment::create([
                    'tweet_id' => $tweet->id,
                    'user_id'  => $user->id,
                    'text'     => "ツイート{$tweet->id}へのテストコメント{$i}（ユーザー{$user->id}）",
                ]);
            }
        }
    }
}