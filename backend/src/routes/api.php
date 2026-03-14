<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\TweetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/withdraw', [AuthController::class, 'withdraw']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::patch('/user', [AuthController::class, 'updateUser']);

    Route::get('/tweets', [TweetController::class, 'index']);
    Route::get('/tweets/{id}', [TweetController::class, 'show']);
    Route::post('/tweets', [TweetController::class, 'store']);
    Route::delete('/tweets/{id}', [TweetController::class, 'destroy']);
    Route::post('/tweets/{id}/comments', [CommentController::class, 'store']);
    Route::post('/tweets/{id}/like', [LikeController::class, 'store']);
    Route::delete('/tweets/{id}/like', [LikeController::class, 'destroy']);
});
