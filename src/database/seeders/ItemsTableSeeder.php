<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $itemsData = [
            [
                "item_name" => "腕時計",
                "price" => 15000,
                "description" => "スタイリッシュなデザインのメンズ腕時計",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg",
                "condition" => "良好",
                "user_id" => 1,
                "brand_id" => 1,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [1, 2],
            ],
            [
                "item_name" => "HDD",
                "price" => 5000,
                "description" => "高速で信頼性の高いハードディスク",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg",
                "condition" => "目立った傷や汚れなし",
                "user_id" => 1,
                "brand_id" => 2,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [3],
            ],
            [
                "item_name" => "玉ねぎ3束",
                "price" => 300,
                "description" => "新鮮な玉ねぎ3束のセット",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg",
                "condition" => "やや傷や汚れあり",
                "user_id" => 1,
                "brand_id" => 3,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [4, 10],
            ],
            [
                "item_name" => "革靴",
                "price" => 4000,
                "description" => "クラシックなデザインの革靴",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg",
                "condition" => "状態が悪い",
                "user_id" => 1,
                "brand_id" => 4,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [5, 6],
            ],
            [
                "item_name" => "ノートPC",
                "price" => 45000,
                "description" => "高性能なノートパソコン",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg",
                "condition" => "良好",
                "user_id" => 1,
                "brand_id" => 5,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [4, 8],
            ],
            [
                "item_name" => "マイク",
                "price" => 8000,
                "description" => "高音質のレコーディング用マイク",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg",
                "condition" => "目立った傷や汚れなし",
                "user_id" => 1,
                "brand_id" => 6,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [6, 7, 10],
            ],
            [
                "item_name" => "ショルダーバッグ",
                "price" => 3500,
                "description" => "おしゃれなショルダーバッグ",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg",
                "condition" => "やや傷や汚れあり",
                "user_id" => 1,
                "brand_id" => 7,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [1, 2, 5],
            ],
            [
                "item_name" => "タンブラー",
                "price" => 500,
                "description" => "使いやすいタンブラー",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg",
                "condition" => "状態が悪い",
                "user_id" => 1,
                "brand_id" => 8,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [9],
            ],
            [
                "item_name" => "コーヒーミル",
                "price" => 4000,
                "description" => "手動のコーヒーミル",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg",
                "condition" => "良好",
                "user_id" => 2,
                "brand_id" => 9,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [10],
            ],
            [
                "item_name" => "メイクセット",
                "price" => 2500,
                "description" => "便利なメイクアップセット",
                "image_url" => "https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg",
                "condition" => "目立った傷や汚れなし",
                "user_id" => 2,
                "brand_id" => 10,
                "sold_at" => null,
                "buyer_id" => null,
                "payment_method" => null,
                "created_at" => now(),
                "updated_at" => now(),
                "category_ids" => [6, 8],
            ],
        ];

        foreach ($itemsData as $data) {
            $categoryIds = $data['category_ids'];
            unset($data['category_ids']);

            $imageUrl = $data['image_url'];
            unset($data['image_url']);

            $imagePath = null;
            try {
                $response = Http::get($imageUrl);

                if ($response->successful()) {
                    $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
                    $filename = urldecode($filename);

                    $storedFileName = 'items/' . $filename;
                    Storage::disk('public')->put($storedFileName, $response->body());

                    $imagePath = 'storage/' . $storedFileName;
                } else {
                    echo "Warning: Failed to download image from " . $imageUrl . " Status: " . $response->status() . "\n";
                }
            } catch (\Exception $e) {
                echo "Error downloading image " . $imageUrl . ": " . $e->getMessage() . "\n";
            }

            $data['image_path'] = $imagePath;
            if (is_null($data['image_path'])) {
                echo "Skipping item creation due to missing image_path for " . $data['item_name'] . "\n";
                continue;
            }

            $item = Item::create($data);
            $item->categories()->attach($categoryIds);
        }
    }
}