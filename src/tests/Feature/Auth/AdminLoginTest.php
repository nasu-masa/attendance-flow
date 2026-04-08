<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser()
    {
        return User::factory()->create([
            'email'    => 'admin@gmail.com',
            'password' => bcrypt('test1234'),
        ]);
    }

    protected function postAdminLogin(array $data)
    {
        return $this->post(route('admin.login.post'), $data);
    }

    protected function assertAdminLoginError($field, $message, array $data)
    {
        $this->createAdminUser();

        $response = $this->postAdminLogin($data);

        $response->assertSessionHasErrors([
            $field => $message,
        ]);
    }

    public function test_メールアドレスが未入力の場合_バリデーションメッセージが表示される()
    {
        $this->assertAdminLoginError(
            'email',
            'メールアドレスを入力してください',
            [
                'email'    => '',
                'password' => 'test1234',
            ]
        );
    }

    public function test_パスワードが未入力の場合_バリデーションメッセージが表示される()
    {
        $this->assertAdminLoginError(
            'password',
            'パスワードを入力してください',
            [
                'email'    => 'admin@gmail.com',
                'password' => '',
            ]
        );
    }

    public function test_登録内容と一致しない場合_バリデーションメッセージが表示される()
    {
        $this->assertAdminLoginError(
            'email',
            'ログイン情報が登録されていません',
            [
                'email'    => 'wrong@gmail.com',
                'password' => 'test4321',
            ]
        );
    }
}
