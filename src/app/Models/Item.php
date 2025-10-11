<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'brand_id',
        'sold_at',
        'buyer_id',
        'payment_method',
    ];

    // Usersテーブルとの紐付け
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Categoriesテーブルとの紐付け
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item', 'item_id', 'category_id')
                    ->withTimestamps();
    }

    // Brandsテーブルとの紐付け
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // buyerとの紐付け
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // コメントとの紐付け
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // いいねとの紐付け
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // いいねの数を取得する
    public function likesCount()
    {
        return $this->likes()->count();
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'item_id');
    }

}
