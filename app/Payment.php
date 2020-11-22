<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\WorkspacePlan;
use App\Subscription;

class Payment extends Model
{
    protected $guarded = [];

    public function plan()
    {
        return $this->belongsTo(WorkspacePlan::class, 'workspace_plan_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
