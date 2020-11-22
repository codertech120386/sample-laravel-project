<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Razorpay extends Model
{
    protected $guarded = [];

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
