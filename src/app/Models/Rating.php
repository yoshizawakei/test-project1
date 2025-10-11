<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    // Mass Assignment を許可するフィールドを定義 (US004, FN012, FN013のデータ)
    protected $fillable = [
        'transaction_id',
        'rater_id',
        'rated_user_id',
        'score',
        'comment',
    ];

    // 評価をしたユーザー (User)
    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    // 評価されたユーザー (User)
    public function ratedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    // 評価が属する取引 (Transaction)
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}