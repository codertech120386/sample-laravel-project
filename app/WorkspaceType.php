<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkspaceType extends Model
{
    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }
}
