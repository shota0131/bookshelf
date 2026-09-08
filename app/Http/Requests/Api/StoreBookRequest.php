<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'author' => [
                'required',
                'string',
                'max:255',
            ],

            'isbn' => [
                'required',
                'string',
                'digits:13',
                'unique:books,isbn',
            ],

            'published_date' => [
                'required',
                'date',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'genres' => [
                'required',
                'array',
                'min:1',
            ],

            'genres.*' => [
                'integer',
                'exists:genres,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'author.required' => '著者名を入力してください。',
            'author.string' => '著者名は文字列で入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',

            'isbn.required' => 'ISBNを入力してください。',
            'isbn.string' => 'ISBNは文字列で入力してください。',
            'isbn.digits' => 'ISBNは13桁で入力してください。',
            'isbn.unique' => 'このISBNはすでに登録されています。',

            'published_date.required' => '出版日を入力してください。',
            'published_date.date' => '出版日は正しい日付で入力してください。',

            'description.string' => '説明は文字列で入力してください。',

            'image_url.url' => '画像URLは正しいURL形式で入力してください。',
            'image_url.max' => '画像URLは255文字以内で入力してください。',

            'user_id.required' => '登録者IDを指定してください。',
            'user_id.integer' => '登録者IDは整数で指定してください。',
            'user_id.exists' => '指定されたユーザーが存在しません。',

            'genres.required' => 'ジャンルを1つ以上指定してください。',
            'genres.array' => 'ジャンルの指定が正しくありません。',
            'genres.min' => 'ジャンルを1つ以上指定してください。',

            'genres.*.integer' => 'ジャンルIDは整数で指定してください。',
            'genres.*.exists' => '指定されたジャンルが存在しません。',
        ];
    }
}
