<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\AuthService;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * 【理由】新規登録後に即ログイン状態へ移行し、登録直後の導線を途切れさせないため。
     * 【制約】登録情報がバリデーション済みであり、ユーザー作成が正常に完了していることを前提とする。
     * 【注意】メール認証前の状態でアクセス可能な画面が限定されるため、遷移先の整合性に注意。
     */
    public function store(RegisterUserRequest $request, AuthService $service)
    {
        $user = $service->registerStaff($request->validated());

        Auth::login($user);

        $user->sendEmailVerificationNotification();

        return view('staff.auth.verify-email');
    }

    /**
     * 【理由】認証成功時にセッションを再生成し、固定セッション攻撃を防ぐため。
     * 【制約】ログイン後のユーザーが staff 権限を持つことを前提とし、権限外ユーザーは即時排除する。
     * 【注意】認証失敗時のエラーメッセージは統一されるため、詳細な理由は利用者に伝わらない点に注意。
     */
    public function login(LoginUserRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        if (!Auth::user()->isStaff()) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'ログイン権限がありません',
            ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->route('staff.attendance.index')
            ->with('success', 'ログインが成功しました');
    }

    /**
     * 【理由】ログアウト時にセッションを完全に破棄し、再利用による不正アクセスを防ぐため。
     * 【制約】セッションが有効であることを前提に、トークン再生成を行う必要がある。
     * 【注意】ログアウト後は固定のログイン画面へ遷移するため、直前の画面には戻れない点に注意。
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
