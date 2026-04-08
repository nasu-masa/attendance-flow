<?php

namespace App\Presenters;

class WorkMessagePresenter
{
    /**
     * 【理由】出勤時の挨拶を時間帯に応じて最適化し、自然な UI 体験を提供するため。
     * 【制約】現在時刻が取得可能であることを前提とし、タイムゾーン設定に依存する。
     * 【注意】時間帯区分を変更する場合は、他のメッセージ生成ロジックとの整合性に注意。
     */
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

    /**
     * 【理由】UIState のアクション名に応じたメッセージ生成を一箇所に集約し、文言の整合性を保つため。
     * 【制約】action が UIState の返す定数と一致していることを前提とする。
     * 【注意】未知の action は空文字を返すため、UIState の定数変更時は対応関係の破綻に注意。
     */
    public function handleAction(string $action, $uiState): string
    {
        return match ($action) {
            $uiState->startAction()    => $this->messageForStartWork(),
            $uiState->breakInAction()  => 'お仕事 お疲れさまです！',
            $uiState->breakOutAction() => '引き続きよろしくお願いします！',
            $uiState->finishAction()   => '今日もお疲れさまでした！',
            default  => tap('', fn() => logger()->warning('Unknown action', ['action' => $action])),
        };
    }
}
