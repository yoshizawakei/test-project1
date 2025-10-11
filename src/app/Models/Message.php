<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    // Mass Assignment を許可するフィールドを定義 (FN008, FN009のデータ)
    protected $fillable = [
        'transaction_id',
        'user_id',
        'content',
        'image_path',
    ];

    // メッセージの投稿者 (User)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // メッセージが属する取引 (Transaction)
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}