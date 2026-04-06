<?php

namespace App\Presenters;

class WorkMessagePresenter
{

    /* ================================
        時間帯に応じた挨拶メッセージ
    ================================= */

    public function messageForStartWork(): string
    {
        $hour = now()->hour;

        if ($hour < 4) {
            return 'こんばんは！';
        } elseif ($hour < 12) {
            return 'おはようございます！今日もよろしくお願いします！';
        } elseif ($hour < 18) {
            return 'こんにちは！今日もよろしくお願いします！';
        } else {
            return 'こんばんは！今日もよろしくお願いします！';
        }
    }

    /* ================================
        操作アクションごとの返答メッセージ
    ================================= */

    public function handleAction(string $action, $uiState): string
    {

        return match ($action) {
            $uiState->startAction()    => $this->messageForStartWork(),
            $uiState->breakInAction()  => 'お仕事 お疲れさまです！',
            $uiState->breakOutAction() => '引き続きよろしくお願いします！',
            $uiState->finishAction()   => '今日もお疲れさまでした！',
            default                    => '',
        };
    }
}
