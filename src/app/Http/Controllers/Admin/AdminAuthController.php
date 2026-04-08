<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Controllers\Controller;

class AdminAuthController extends Controller
{
    /**
     * 【理由】管理者専用のログイン画面を明示的に分離し、一般ユーザーと混同しない導線を維持するため。
     * 【制約】管理者用ルートとして適切に保護されていることが前提となる。
     * 【注意】画面表示のみを行うため、認証状態や権限チェックは呼び出し側のルーティング設定に依存する。
     */
    public function showLogin()
    {
        return view('admin.auth.login');
    }


    /**
     * 【理由】認証情報の正当性と管理者権限の有無を二段階で確認し、不正ログインや権限外アクセスを防ぐため。
     * 【制約】LoginUserRequest により email/password が正しく検証されていることが前提となる。
     * 【注意】権限不足時は即ログアウトするため、セッション状態が途中で変化する点に注意。
     */
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


    /**
     * 【理由】管理者セッションを確実に破棄し、ログアウト後の不正利用を防ぐため。
     * 【制約】セッションストアが有効に動作していることが前提となる。
     * 【注意】トークン再生成により既存フォームの CSRF トークンが無効化されるため、ログアウト直後の再送信は失敗する。
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
