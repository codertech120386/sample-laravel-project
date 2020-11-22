<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkspacePlan extends Model
{
    protected $mapping = [
        'properties' => [
            'id' => [
                'type' => 'integer',
                'type' => 'keyword'
            ],
            'workspace_id' => [
                'type' => 'integer',
                'type' => 'keyword'
            ],
            'space_type' => [
                'type' => 'text',
                'analyzer' => 'english'
            ],
            'title' => [
                'type' => 'text',
                'analyzer' => 'english'
            ],
            'cost' => [
                'type' => 'integer',
                'type' => 'keyword'
            ]
        ]
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications()
    {
        return $this->morphMany(AppNotification::class, 'notifiable');
    }
}
