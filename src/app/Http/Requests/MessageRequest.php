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
            // ★ imageに対するバリデーションルールを追加/確認 ★
            'image' => [
                'nullable', // 画像は必須ではない
                'image',    // ファイルが有効な画像であること
                'mimes:jpeg,png', // 許可するファイル形式
                'max:2048', // 最大ファイルサイズ（2MB）
            ],
        ];
    }

    // エラーメッセージの日本語化
    public function messages(): array
    {
        return [
            'content.required' => '本文を入力してください。',
            'content.max' => '本文は400文字以内で入力してください。',
            'image.mimes' => 'jpeg「またはpngのみ対応しています。',
        ];
    }

}
