<?php

namespace Tests\Unit;

use App\Presenters\BasePresenter;
use Tests\TestCase;

class CalculationTest extends TestCase
{
    // 不具合が出やすい箇所に絞りテスト
    public function test_時刻整形ロジックの検証()
    {
        $this->assertSame('0:45', BasePresenter::formatMinutes(45));
        $this->assertSame('1:00', BasePresenter::formatMinutes(60));

        $this->assertSame('', BasePresenter::formatMinutes(null));
    }
}