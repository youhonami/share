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
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // ログイン用パスワード
            'remember_token' => Str::random(10),
        ]);

        User::create([
            'name' => 'テストユーザー2',
            'email' => 'user2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        User::create([
            'name' => 'テストユーザー3',
            'email' => 'user3@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);
    }
}