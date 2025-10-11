<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            // score (評価点) は必須、整数で1から5の範囲
            'score' => ['required', 'integer', 'min:1', 'max:5'],
            // comment (感想) は必須ではないが、もし必須であれば 'required' を追加
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }
}