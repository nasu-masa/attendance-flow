<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STAFF,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('staff.attendance.index'));

        $expectedDate = now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');

        $response->assertSee($expectedDate);
    }
}
