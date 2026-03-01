<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id(); // bigint unsigned
            $table->foreignId('tweet_id') // bigint unsigned
                  ->constrained('tweets')
                  ->onDelete('cascade');
            $table->foreignId('user_id') // bigint unsigned
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['tweet_id', 'user_id']); // 同じユーザーが同じ投稿に複数いいねできないように
        });
    }

    public function down()
    {
        Schema::dropIfExists('likes');
    }
}