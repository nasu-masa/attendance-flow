<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * 【理由】ログインに必要な最小限の認証情報を確実に受け取り、不正な形式の入力を防ぐため。
     * 【制約】email は有効な形式であることを前提とし、password は空でない文字列である必要がある。
     * 【注意】認証可否はここでは判定されないため、形式が正しくてもログインに失敗する可能性がある。
     */
    public function rules()
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'email.required'    => 'メールアドレスを入力してください',
            'email.email'       => 'メールアドレスはメールの形式で入力してください',

            'password.required' => 'パスワードを入力してください',
        ];
    }

}
