<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Payment;

class Subscription extends Model
{
    protected $guarded = [];
    protected $dates = ['start_date', 'end_date'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
