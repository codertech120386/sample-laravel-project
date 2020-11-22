<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CouponCode extends Model
{
    public function scopeInQuantity($query)
    {
        return $query->whereNull('quantity')->orWhere('quantity', '>', 0);
    }

    public function scopeNotExpired($query)
    {
        return $query->whereNull('expires_at')->orWhereDate('expires_at', '>', Carbon::now());
    }

    public function notifications()
    {
        return $this->morphMany(AppNotification::class, 'notifiable');
    }
}
