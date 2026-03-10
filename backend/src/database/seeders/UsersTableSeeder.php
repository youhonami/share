<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {

        User::create([
            'name' => 'テストユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'), // ログイン用パスワード
        ]);

        User::create([
            'name' => 'テストユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'テストユーザー3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}