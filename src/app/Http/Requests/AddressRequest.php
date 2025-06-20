<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "username" => "required|string|max:255",
            "postal_code" => "required|regex:/^\d{3}-\d{4}$/",
            "address" => "required|string|max:255",
            "building_name" => "nullable|string|max:255",
        ];
    }

    public function messages()
    {
        return [
            "username.required" => "ユーザー名を入力してください。",
            "username.string" => "ユーザー名は文字列で入力してください。",
            "postal_code.required" => "郵便番号を入力してください。",
            "postal_code.string" => "郵便番号は文字列で入力してください。",
            "postal_code.regex" => "郵便番号はハイフンを入力してください。",
            "address.required" => "住所を入力してください。",
            "address.string" => "住所は文字列で入力してください。",
            "address.max" => "住所は255文字以内で入力してください。",
            "building_name.string" => "建物名は文字列で入力してください。",
            "building_name.max" => "建物名は255文字以内で入力してください。",
        ];
    }
}
