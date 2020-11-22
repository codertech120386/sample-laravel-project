<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Workspace;

class Amenity extends Model
{
    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }
}
