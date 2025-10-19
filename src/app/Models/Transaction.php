<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    // Mass Assignment を許可するフィールドを定義
    protected $fillable = [
        'item_id',
        'seller_id',
        'buyer_id',
        'status',
        'stripe_session_id', // 決済情報

        'completed_at', // 取引完了日時
        'buyer_rating_id', // 購入者による評価ID
        'seller_rating_id', // 出品者による評価ID
    ];

    // 取引に関連する商品
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // 出品者 (User)
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // 購入者 (User)
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // 取引履歴のメッセージ
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}