<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;

use App\Configurators\SearchWorkspaceIndexConfigurator;

class Workspace extends Model
{
    use Searchable;

    protected $searchRules = [
        SearchWorkspaceRule::class
    ];

    public function toSearchableArray()
    {
        $new_array = $this->toArray();

        if (count($this->plans)) {
            $space_types_with_duplicates = $this->plans->pluck('space_type')->toArray();
            $plan_types_with_duplicates = $this->plans->pluck('title')->toArray();
            $plan_types = array_values(array_unique($plan_types_with_duplicates));
            $space_types = array_values(array_unique($space_types_with_duplicates));

            $cheapest_plan = $this->plans()->orderBy('cost')->first();

            if ($cheapest_plan) {

                $new_array = array_merge(
                    $new_array,
                    ['per_day' => $cheapest_plan->cost, 'space_types' => $space_types, 'plan_types' => $plan_types]
                );
            }
        }
        if (count($this->weekly_schedules)) {
            $weekly_schedules = $this->weekly_schedules()->whereNotNull('opens_at')->first();
            $opens_at = $weekly_schedules->opens_at;
            $closes_at = $weekly_schedules->closes_at;

            $new_array = array_merge(
                $new_array,
                ['opens_at' => $opens_at, 'closes_at' => $closes_at]
            );
        }
        if ($this->type) {
            $new_array = array_merge(
                $new_array,
                ['workspace_type' => $this->type->name]
            );
        }

        if ($this->amenities) {
            $new_array = array_merge(
                $new_array,
                ['amenities' => $this->amenities->pluck('name')]
            );
        }
        if ($this->images) {
            $new_array = array_merge(
                $new_array,
                ['image_urls' => $this->images->pluck('image_url')]
            );
        }
        if ($this->addresses) {
            $address = $this->addresses()->first();
            $new_array = array_merge(
                $new_array,
                [
                    'address' => $address->address,
                    'short_address' => $address->short_address,
                    'location' => $address->lat . "," . $address->long
                ]
            );
        }

        return $new_array;
    }

    protected $indexConfigurator = SearchWorkspaceIndexConfigurator::class;

    protected $mapping = [
        'properties' => [
            'name' => [
                'type' => 'text',
                'analyzer' => 'english',
            ],
            'description' => [
                'type' => 'text',
                'analyzer' => 'english',
            ],
            'address' => [
                'type' => 'text',
                'analyzer' => 'english'
            ],
            'location' => [
                'type' => "geo_point"
            ]
        ]
    ];

    public function images()
    {
        return $this->hasMany(WorkspaceImage::class);
    }

    public function banners()
    {
        return $this->hasMany(WorkspaceBanner::class);
    }

    public function plans()
    {
        return $this->hasMany(WorkspacePlan::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function weekly_schedules()
    {
        return $this->hasMany(WeeklySchedule::class);
    }

    public function date_schedules()
    {
        return $this->hasMany(DateSchedule::class);
    }

    public function type()
    {
        return $this->belongsTo(WorkspaceType::class, 'workspace_type_id');
    }

    public function addresses()
    {
        return $this->hasMany(WorkspaceAddress::class);
    }
}
