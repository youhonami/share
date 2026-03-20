<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBlocksTable extends Migration
{
    public function up()
    {
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('blocked_user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();

            // 同一ユーザーが同じ相手を重複ブロックできない
            $table->unique(['user_id', 'blocked_user_id']);
            $table->index('blocked_user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_blocks');
    }
}
