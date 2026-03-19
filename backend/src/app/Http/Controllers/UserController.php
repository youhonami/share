<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * ブロック候補などに使うユーザー一覧（ログイン中の自分は除く）
     */
    public function index(Request $request)
    {
        return User::query()
            ->where('id', '!=', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
