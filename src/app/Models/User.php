<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_configured',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'profile_configured' => 'boolean',

    ];

    // このユーザーが購入した商品とのリレーション
    public function purchasedItems(): HasMany
    {
        return $this->hasMany(Item::class, 'buyer_id');
    }

    // このユーザーが出品した商品とのリレーション
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    // このユーザーのプロフィールとのリレーション
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // このユーザーがコメントした商品とのリレーション
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // このユーザーがいいねした商品とのリレーション
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    // いいねしているかどうかを確認するメソッド
    public function isLiking(Item $item)
    {
        return $this->likes()->where("item_id", $item->id)->exists();
    }
}
