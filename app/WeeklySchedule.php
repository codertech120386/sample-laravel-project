<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Workspace;

class WeeklySchedule extends Model
{
    public function workspaces()
    {
        return $this->belongsTo(Workspace::class);
    }
}
