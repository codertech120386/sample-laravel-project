<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Workspace;

class DateSchedule extends Model
{
    public function workspaces()
    {
        return $this->belongsTo(Workspace::class);
    }
}
