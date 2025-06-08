<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // ユーザーテーブルのシーダーを呼び出す
        $this->call([
            UsersTableSeeder::class,
        ]);
        $this->call([
            CategoriesTableSeeder::class,
        ]);

        Brand::factory(10)->create();

        $this->call([
            ItemsTableSeeder::class,
        ]);
    }
}
