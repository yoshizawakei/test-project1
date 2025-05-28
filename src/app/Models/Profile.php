<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'profile_image',
        'postal_code',
        'address',
        'building_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
