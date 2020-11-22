<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RecentlySearchedWorkspace extends Pivot
{
    protected $table = "recently_searched_workspaces";

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
