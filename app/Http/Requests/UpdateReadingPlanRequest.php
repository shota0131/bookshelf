<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingPlanRequest extends FormRequest
{
    /**
     * 認証済みユーザーのみ許可する。
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * バリデーションルール。
     */
    public function rules(): array
    {
        return [
            'target_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /**
     * エラーメッセージ。
     */
    public function messages(): array
    {
        return [
            'target_date.required' => '目標日を入力してください。',
            'target_date.date' => '正しい日付を入力してください。',
            'target_date.after_or_equal' => '目標日は今日以降の日付を指定してください。',
        ];
    }
}
