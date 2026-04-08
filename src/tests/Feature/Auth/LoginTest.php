<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestUser()
    {
        return User::factory()->create([
            'email'    => 'test@gmail.com',
            'password' => bcrypt('test1234'),
        ]);
    }

    protected function postLogin(array $data)
    {
        return $this->post(route('staff.login.post'), $data);
    }

    protected function assertLoginError($field, $message, array $data)
    {
        $this->createTestUser();

        $response = $this->postLogin($data);

        $response->assertSessionHasErrors([
            $field => $message,
        ]);
    }

    public function test_メールアドレスが未入力の場合_バリデーションメッセージが表示される()
    {
        $this->assertLoginError(
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
        $this->assertLoginError(
            'password',
            'パスワードを入力してください',
            [
                'email'    => 'test@gmail.com',
                'password' => '',
            ]
        );
    }

    public function test_登録内容と一致しない場合_バリデーションメッセージが表示される()
    {
        $this->assertLoginError(
            'email',
            'ログイン情報が登録されていません',
            [
                'email'    => 'wrong@gmail.com',
                'password' => 'test1234',
            ]
        );
    }
}
