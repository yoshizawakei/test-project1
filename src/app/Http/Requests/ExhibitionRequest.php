<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            "item_name" => "required|string",
            "description" => "required|string|max:255",
            "image" => "required|file|mimes:jpeg,png",
            "category_ids" => "required|min:1",
            "category_ids.*" => "exists:categories,id",
            "condition" => "required|string|in:良好,目立った傷や汚れなし,やや傷や汚れあり,状態が悪い",
            "price" => "required|numeric|min:0",
            "brand_id" => "nullable|exists:brands,id",
        ];
    }

    public function messages()
    {
        return [
            "item_name.required" => "商品名は必ず入力してください。",
            "description.required" => "商品の説明は必ず入力してください。",
            "description.max" => "商品の説明は255文字以内で入力してください。",
            "image.required" => "商品画像は必ずアップロードしてください。",
            "image.file" => "商品画像はファイル形式でアップロードしてください。",
            "image.mimes" => "商品画像の拡張子はjpegまたはpngにしてください。",
            "category_ids.required" => "商品のカテゴリーを少なくとも1つ選択してください。",
            "category_ids.min" => "商品のカテゴリーを少なくとも1つ選択してください。",
            "category_ids.*.exists" => "選択されたカテゴリーが無効です。",
            "condition.required" => "商品の状態を必ず選択してください。",
            "price.required" => "商品価格は必ず入力してください。",
            "price.numeric" => "商品価格は数値で入力してください。",
            "price.min" => "商品価格は0円以上で入力してください。",
            "brand_id.exists" => "選択されたブランドが無効です。"
        ];
    }
}
