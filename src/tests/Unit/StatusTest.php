<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Tests\TestCase;

class StatusTest extends TestCase
{
    public function test_CorrectionRequestの定数が正しい()
    {
        $this->assertSame('pending', CorrectionRequest::STATUS_PENDING);
        $this->assertSame('approved', CorrectionRequest::STATUS_APPROVED);
    }

    public function test_Attendanceの定数が正しい()
    {
        $this->assertSame('working', Attendance::STATUS_WORKING);
        $this->assertSame('start', Attendance::ACTION_START);
    }
}
