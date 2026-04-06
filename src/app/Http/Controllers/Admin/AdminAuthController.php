<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Controllers\Controller;

class AdminAuthController extends Controller
{
    /* ================================
        PG07：管理者ログイン画面
    ================================= */

    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(LoginUserRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        if (!Auth::user()->isAdmin()) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'ログイン権限がありません',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.attendance.list')
                ->with('success', 'ログインが成功しました');
        }

        return back();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
