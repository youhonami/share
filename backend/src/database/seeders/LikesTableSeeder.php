<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;

class LikesTableSeeder extends Seeder
{
    public function run()
    {
        $tweets = Tweet::all();
        $users  = User::all();

        if ($tweets->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($tweets as $tweet) {
            // 各ツイートにランダムな 1〜3 ユーザーがいいねする
            // shuffle()->take() を使うことで、常に Collection を返し、
            // 1件だけのときでも単一モデルにならないようにする
            $likeUsers = $users->shuffle()->take(min(3, $users->count()));

            foreach ($likeUsers as $user) {
                // unique 制約（tweet_id, user_id）に合わせて create
                Like::firstOrCreate([
                    'tweet_id' => $tweet->id,
                    'user_id'  => $user->id,
                ]);
            }
        }
    }
}