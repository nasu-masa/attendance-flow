<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * 【理由】登録に必要な属性の品質を保証し、不正なメール形式や弱いパスワードを防ぐため。
     * 【制約】email は DNS 検証を前提とし、password は確認用入力との一致が必須となる。
     * 【注意】name の最大文字数や email の一意性は UI 表示や登録可否に直接影響する点に注意。
     */
    public function rules()
    {
        return [
            'name'      => ['required', 'string', 'max:20'],
            'email'     => ['required', 'string', 'email:rfc,dns', 'unique:users,email'],
            'password'  => ['required', 'string', 'confirmed', Password::min(8)],
            'password_confirmation' => ['required', 'string']
        ];
    }

    public function messages()
    {
        return [
            'name.required'       => 'お名前を入力してください',
            'name.max'            => 'お名前は:max文字以下で入力してください',

            'email.required'      => 'メールアドレスを入力してください',
            'email.email'         => 'メールアドレスはメールの形式で入力してください',

            'password.required'   => 'パスワードを入力してください',
            'password.min'        => 'パスワードは:min文字以上で入力してください',
            'password.confirmed'  => 'パスワードと一致しません',

            'password_confirmation.required' => '確認用パスワードを入力してください',
        ];
    }

    /**
     * 【理由】登録処理に必要な属性のみを抽出し、後続処理へ安全に渡すため。
     * 【制約】入力値がバリデーション済みであることを前提に、パスワードをハッシュ化して生成する。
     * 【注意】返却される配列は登録専用の構造であり、他用途での流用は前提としていない。
     */
    public function toRegisterAttributes()
    {
        return [
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make($this->password)
        ];
    }
}
