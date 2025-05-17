<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;
    protected $fillable = [
        'item_name',
        'price',
        'description',
        'image_path',
        'condition',
        'user_id',
        'category_id',
        'brand_id',
        'status_id',
        'color_id',
        'sold_at',
        'buyer_id',
    ];

    // Usersテーブルとの紐付け
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Categoriesテーブルとの紐付け
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Brandsテーブルとの紐付け
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // Statusesテーブルとの紐付け
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // Colorsテーブルとの紐付け
    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    // buyerとの紐付け
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
