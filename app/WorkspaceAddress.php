<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkspaceAddress extends Model
{
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
