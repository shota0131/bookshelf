<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
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
            'book_id' => [
                'required',
                'exists:books,id',
                'unique:reading_plans,book_id,NULL,id,user_id,' . auth()->id(),
            ],

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
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '選択した書籍が存在しません。',
            'book_id.unique' => 'この書籍の読書計画はすでに登録されています。',

            'target_date.required' => '目標日を入力してください。',
            'target_date.date' => '正しい日付を入力してください。',
            'target_date.after_or_equal' => '目標日は今日以降の日付を指定してください。',
        ];
    }
}