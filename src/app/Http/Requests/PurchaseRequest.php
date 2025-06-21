<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            "payment_method" => "required|string|in:credit_card,convenience_store",
            "user_profile_exists" => "accepted",
            // "user_profile_configured" => "accepted",
        ];
    }

    public function messages()
    {
        return [
            "payment_method.required" => "支払い方法を選択してください。",
            "payment_method.in" => "支払い方法はクレジットカードまたはコンビニ決済から選択してください。",
            "user_profile_exists.accepted" => "ユーザープロフィールが設定されていません。プロフィールを設定してください。",
            // "user_profile_configured.accepted" => "ユーザープロフィールが設定されていません。プロフィールを設定してください。",
        ];
    }


}
