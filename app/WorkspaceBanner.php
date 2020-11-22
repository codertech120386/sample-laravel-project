<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkspaceBanner extends Model
{
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
