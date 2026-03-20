<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    /**
     * ログインユーザーがブロックしているユーザー一覧
     */
    public function index(Request $request)
    {
        $users = $request->user()
            ->blockedUsers()
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);

        return $users->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
        ])->values();
    }

    /**
     * ユーザーをブロックする
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'blocked_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        $targetId = (int) $validated['blocked_user_id'];

        if ($targetId === $user->id) {
            return response()->json([
                'message' => '自分自身はブロックできません。',
            ], 422);
        }

        if (! $user->blockedUsers()->where('users.id', $targetId)->exists()) {
            $user->blockedUsers()->attach($targetId);
        }

        $blocked = User::query()->findOrFail($targetId);

        return response()->json([
            'user' => [
                'id' => $blocked->id,
                'name' => $blocked->name,
                'email' => $blocked->email,
            ],
        ], 201);
    }

    /**
     * ブロックを解除する
     */
    public function destroy(Request $request, int $blockedUserId)
    {
        $request->user()->blockedUsers()->detach($blockedUserId);

        return response()->noContent();
    }
}
