<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_名前が未入力の場合_バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'     => '',
            'email'    => 'test@gmail.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234'
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    public function test_メールアドレスが未入力の場合_バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'     => '茄子田常夫',
            'email'    => '',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_パスワードが8文字未満の場合_バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'     => '茄子田常夫',
            'email'    => 'test@gmail.com',
            'password' => 'test123',
            'password_confirmation' => 'test123',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    public function test_パスワードが一致しない場合_バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'     => '茄子田常夫',
            'email'    => 'test@gmail.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1111',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    public function test_パスワードが未入力の場合_バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'     => '茄子田常夫',
            'email'    => 'test@gmail.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_フォームに内容が入力されていた場合_データが正常に保存される()
    {
        $this->post('/register', [
            'name'     => '茄子田常夫',
            'email'    => 'test@gmail.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@gmail.com'
        ] );
    }
}
