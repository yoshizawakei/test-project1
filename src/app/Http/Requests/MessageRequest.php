<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            // contentがない場合はimageが必須 (required_without)
            'content' => ['required_without:image', 'string', 'max:400'],

            // imageがない場合はcontentが必須 (required_without)。nullableで本文がある場合は省略可。
            'image' => [
                'required_without:content',
                'nullable',
                'image',
                'mimes:jpeg,png',
                'max:2048', // 2MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required_without' => '本文を入力してください',
            'image.required_without' => '',

            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',

            'content.max' => '本文は400文字以内で入力してください',

            'image.max' => '画像ファイルのサイズは2MB以内でアップロードしてください。',
            'image.image' => 'アップロードされたファイルは画像である必要があります。',
        ];
    }
}
