<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録後_認証メールが送信される()
    {
        Notification::fake();

        $this->post('/register', [
            'name'                  => 'test',
            'email'                 => 'verifytest@gmail.com',
            'password'              => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'verifytest@gmail.com',
        ]);

        $user = User::where('email', 'verifytest@gmail.com')->first();

        Notification::assertSentTo($user, CustomVerifyEmail::class);
        $this->assertNull($user->email_verified_at);
    }

    public function test_メール認証誘導画面で認証はこちらからボタンを押下するとメール認証サイトに遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/email/verify');

        $response->assertSee('認証はこちらから');
    }

    public function test_メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id'   => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($url);

        $response->assertRedirect(route('staff.attendance.index'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
