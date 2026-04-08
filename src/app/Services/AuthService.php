<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * 【理由】スタッフ登録時のロール付与とパスワードハッシュ化を一箇所に集約し、Controller の責務を単純化するため。
     * 【制約】必要な属性（name・email・password）が揃っていることを前提にユーザー作成を行う。
     * 【注意】ロールが固定で付与されるため、他ロールを扱う場合は別メソッドを用意する必要がある。
     */
    public function registerStaff(array $attributes): User
    {
        return User::create([
            'name'     => $attributes['name'],
            'email'    => $attributes['email'],
            'password' => Hash::make($attributes['password']),
            'role'     => User::ROLE_STAFF,
        ]);
    }
}