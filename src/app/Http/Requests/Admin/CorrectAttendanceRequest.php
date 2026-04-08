<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CorrectAttendanceRequest extends FormRequest
{
    /**
     * 【理由】未入力の時間項目を null に統一し、バリデーションが期待する前提を満たすため。
     * 【制約】nullable・required_with 判定が null を基準に動作するため、値の正規化が必須となる。
     * 【注意】空文字や未送信値が混在すると判定が不安定になるため、ここでの統一処理に依存する。
     */
    protected function prepareForValidation()
    {
        foreach (
            [
                'clock_in',
                'clock_out',
                'break_start_1',
                'break_end_1',
                'break_start_2',
                'break_end_2'
            ] as $field
        ) {
            if (!$this->filled($field)) {
                $this->merge([$field => null]);
            }
        }
    }

    /**
     * 【理由】この修正リクエストが admin 専用である前提を保証し、不正権限での更新を防ぐため。
     * 【制約】認証済みユーザーが存在し、ロール判定が可能であることを前提とする。
     * 【注意】false の場合は自動的に 403 となるため、UI 側での制御は行われない点に注意。
     */
    public function authorize()
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /**
     * 【理由】勤怠修正に必要な時間項目の整合性を保ち、矛盾した入力を防ぐため。
     * 【制約】休憩開始・終了は相互依存するため、required_with によるペア入力が前提となる。
     * 【注意】時系列制約により clock_in → break → clock_out の順序が崩れるとバリデーションが失敗する。
     */
    public function rules()
    {
        return [
            'clock_in'      => ['required', 'date_format:H:i'],
            'clock_out'     => ['required', 'date_format:H:i', 'after:clock_in'],

            'break_start_1' => [
                'nullable',
                'date_format:H:i',
                'after:clock_in',
                'before:clock_out',
                'required_with:break_end_1',
            ],

            'break_end_1' => [
                'nullable',
                'date_format:H:i',
                'after:break_start_1',
                'before:clock_out',
                'required_with:break_start_1',
            ],

            'break_start_2' => [
                'nullable',
                'date_format:H:i',
                'after:clock_in',
                'after:break_end_1',
                'before:clock_out',
                'required_with:break_end_2',
            ],

            'break_end_2' => [
                'nullable',
                'date_format:H:i',
                'after:break_start_2',
                'before:clock_out',
                'required_with:break_start_2',
            ],

            'remarks' => ['required', 'string', 'max:500']
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required'     => '出勤時間を入力してください',
            'clock_in.date_format'  => '出勤時間は「HH:MM」の形式で入力してください（例：08:00）',

            'clock_out.required'    => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間は「HH:MM」の形式で入力してください（例：17:00）',
            'clock_out.after'       => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start_1.required_with' => '休憩の開始時間を入力してください（終了を入力した場合は必須です）',
            'break_start_1.date_format'   => '休憩開始時間は「HH:MM」の形式で入力してください（例：12:00）',
            'break_start_1.after'         => '休憩時間が不適切な値です',
            'break_start_1.before'        => '休憩時間が不適切な値です',

            'break_end_1.required_with' => '休憩の終了時間を入力してください（開始を入力した場合は必須です）',
            'break_end_1.date_format'   => '休憩終了時間は「HH:MM」の形式で入力してください（例：13:00）',
            'break_end_1.after'         => '休憩時間が不適切な値です',
            'break_end_1.before'        => '休憩時間もしくは退勤時間が不適切な値です',

            'break_start_2.required_with' => '休憩2の開始時間を入力してください（終了を入力した場合は必須です）',
            'break_start_2.date_format'   => '休憩2の開始時間は「HH:MM」の形式で入力してください（例：15:00）',
            'break_start_2.after'         => '休憩時間が不適切な値です',
            'break_start_2.before'        => '休憩時間が不適切な値です',

            'break_end_2.required_with' => '休憩2の終了時間を入力してください（開始を入力した場合は必須です）',
            'break_end_2.date_format'   => '休憩2の終了時間は「HH:MM」の形式で入力してください（例：15:15）',
            'break_end_2.after'         => '休憩時間が不適切な値です',
            'break_end_2.before'        => '休憩時間もしくは退勤時間が不適切な値です',

            'remarks.required' => '備考を記入してください',
            'remarks.string'   => '備考は文字列で入力してください',
            'remarks.max'      => '備考は500文字以内で入力してください'
        ];
    }
}

