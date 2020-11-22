<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkspaceImage extends Model
{
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
