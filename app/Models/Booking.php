<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'booking_date',
        'booking_time',
        'name',
        'phone',
        'guests',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
