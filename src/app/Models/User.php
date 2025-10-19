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


    public function getProfileImagePathAttribute()
    {
        if ($this->profile && $this->profile->profile_image) {
            return 'storage/' . $this->profile->profile_image;
        }

        return 'img/logo.svg';
    }

    // このユーザーが関わる取引（出品側）
    public function sellingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    // このユーザーが関わる取引（購入側）
    public function buyingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    // このユーザーが他のユーザーに行った評価
    public function ratingsGiven(): HasMany
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    // このユーザーが他のユーザーから受けた評価
    public function ratingsReceived(): HasMany
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }
}
