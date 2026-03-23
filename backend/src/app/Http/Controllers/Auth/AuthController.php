<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateUserNameRequest;
use App\Http\Requests\Auth\WithdrawRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle a registration request for the application.
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $user,
        ], 201);
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['認証に失敗しました。'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Handle account withdrawal.
     */
    public function withdraw(WithdrawRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        // ログイン中ならセッションを破棄
        if (Auth::check() && Auth::id() === $user->id) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // トークンを削除してからユーザー削除（投稿・コメント・いいねはDBのCASCADEで削除される）
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => '退会が完了しました。',
        ]);
    }

    /**
     * Update the authenticated user's name.
     */
    public function updateUser(UpdateUserNameRequest $request)
    {
        $validated = $request->validated();

        $user = $request->user();
        $user->name = $validated['name'];
        $user->save();

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $validated = $request->validated();

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['現在のパスワードが正しくありません。'],
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'message' => 'パスワードを変更しました。',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}

