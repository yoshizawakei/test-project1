<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("categories")->insert([
            [
                "name" => "ファッション",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "家電",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "インテリア",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "レディース",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "メンズ",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "コスメ",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "本",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "ゲーム",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "スポーツ",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "キッチン",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "ハンドメイド",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "アクセサリー",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "おもちゃ",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "ベビー・キッズ",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
