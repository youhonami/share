<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // bigint unsigned
            $table->foreignId('tweet_id') // bigint unsigned
                  ->constrained('tweets')
                  ->onDelete('cascade');
            $table->foreignId('user_id') // bigint unsigned
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->text('text'); // text
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
}