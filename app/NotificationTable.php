<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = "app_notifications";

    protected $guarded = [];

    public function notifiable()
    {
        return $this->morphTo();
    }
}
