<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MessageRequest extends FormRequest
{
    /**
     * 認証ユーザーのみリクエストを許可
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * バリデーションルールを定義
     */
    public function rules(): array
    {
        return [
            'content' => ['required_without:image', 'string', 'max:400'],
            'image' => [
                'required_without:content',
                'nullable',
                'image',
                'mimes:jpeg,png',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required_without' => '本文を入力してください。',
            'image.required_without' => '',
            'content.max' => '本文は400文字以内で入力してください。',
            'image.mimes' => 'jpegまたはpngのみ対応しています。',
            'image.max' => '画像ファイルのサイズは2MB以内でアップロードしてください。',
        ];
    }
}
