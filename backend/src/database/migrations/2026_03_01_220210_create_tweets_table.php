<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTweetsTable extends Migration
{
    public function up()
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->id(); // bigint unsigned
            $table->foreignId('user_id') // bigint unsigned
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->text('text'); // text
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tweets');
    }
}