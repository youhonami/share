<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tweet;
use App\Models\User;

class TweetsTableSeeder extends Seeder
{
    public function run()
    {
        // まず全ユーザーを取得（UsersTableSeeder で作成済み前提）
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // 各ユーザーに 3 件ずつツイートを作成
            for ($i = 1; $i <= 3; $i++) {
                Tweet::create([
                    'user_id' => $user->id,
                    'text'    => "ユーザー{$user->id}のテストツイート{$i}",
                ]);
            }
        }
    }
}