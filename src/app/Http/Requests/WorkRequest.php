<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'break_start' => ['array'],
            'break_start.*' => ['nullable', 'date_format:H:i'],
            'break_end' => ['array'],
            'break_end.*' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'reason.required' => '備考を記入してください',
            'reason.string' => '備考は文字で入力してください',
            'reason.max' => '備考は255文字以内で記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;

            $breakStarts = $this->break_start ?? [];
            $breakEnds = $this->break_end ?? [];

            //出勤時刻が退勤時刻より遅い場合、または退勤時間が出勤時間より早い場合NG
            if (!is_null($clockIn) && !is_null($clockOut) && $clockIn > $clockOut) {
                $validator->errors()->add(
                    "clock_in",
                    "出勤時間もしくは退勤時間が不適切な値です"
                );
            }
            // 休憩時間
            foreach ($breakStarts as $i => $start) {
                $end = $breakEnds[$i] ?? null;

                // 休憩開始時間が入力されている場合
                if (!is_null($start)) {
                    //出勤時間より早い時間はNG
                    if (!is_null($clockIn) && $start < $clockIn) {
                        $validator->errors()->add(
                            "break_start.$i",
                            "休憩時間が不適切な値です"
                        );
                    }
                    // 退勤時間より遅い時間はNG
                    if (!is_null($clockOut) && $start > $clockOut) {
                        $validator->errors()->add(
                            "break_start.$i",
                            "休憩時間が不適切な値です"
                        );
                    }

                    // 休憩終了時間が未入力
                    if (is_null($end)) {
                        $validator->errors()->add(
                            "break_end.$i",
                            "休憩終了時間を入力してください",
                        );
                    }

                    // 休憩開始時間が休憩終了時間よりも遅い場合はNG
                    if (!is_null($end) && $start > $end) {
                        $validator->errors()->add(
                            "break_start.$i",
                            "休憩時間が不適切な値です"
                        );
                    }
                }

                // 休憩終了時間が入力されている場合
                if (!is_null($end)) {
                    // 退勤時間より遅い時間はNG
                    if (!is_null($clockOut) && $end > $clockOut) {
                        $validator->errors()->add(
                            "break_end.$i",
                            "休憩時間もしくは退勤時間が不適切な値です"
                        );
                    }

                    // 休憩開始時間が未入力
                    if (is_null($start)) {
                        $validator->errors()->add(
                            "break_start.$i",
                            "休憩開始時間を入力してください",
                        );
                    }
                }
            }
        });
    }
}
