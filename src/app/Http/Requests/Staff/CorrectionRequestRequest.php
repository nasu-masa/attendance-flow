<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequestRequest extends FormRequest
{
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

    public function authorize()
    {
        return auth()->user()?->isStaff() ?? false;
    }

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

            'remarks'       => ['required', 'string', 'max:500']
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required'     => '出勤時間を入力してください',
            'clock_in.date_format'  => '出勤時間は「HH:MM」の形式で入力してください（例：09:00）',

            'clock_out.required'    => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間は「HH:MM」の形式で入力してください（例：18:00）',
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
