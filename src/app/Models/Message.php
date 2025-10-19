<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'content',
        'image_path',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
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