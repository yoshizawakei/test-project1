<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Status;
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
        User::factory(10)->create();
        Brand::factory(10)->create();
        Category::factory(10)->create();
        Color::factory(10)->create();
        Status::factory(10)->create();

        $this->call([
            ItemsTableSeeder::class,
        ]);
        // \App\Models\User::factory(10)->create();
    }
}
