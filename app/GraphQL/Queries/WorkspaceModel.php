<?php

namespace App\GraphQL\Queries;

use App\Subscription;
use Carbon\Carbon;

use App\Workspace;
use App\WorkspaceType;
use App\UserToken;
use App\Exceptions\AuthenticationException;
use App\RecentlySearchedWorkspace;

class WorkspaceModel
{
    public function get_date_schedules($rootValue, array $args)
    {
        $workspace = Workspace::find($args['workspace_id']);
        $todaysSchedule = $workspace->date_schedules()->where('date', $args['date'])->first();

        if (!$todaysSchedule) {
            $today = Carbon::now()->format('l');
            $todaysSchedule = $workspace->weekly_schedules()->where('day', $today)->first();
        }
        return $todaysSchedule;
    }

    public function get_workspaces($rootValue, array $args)
    {
        $query = Workspace::with(['type', 'banners', 'images', 'plans', 'weekly_schedules']);

        if (isset($args['type'])) {
            $query = $query->join('workspace_types', 'workspace_types.id', '=', 'workspaces.workspace_type_id')
                ->where('workspace_types.name', $args['type']);
        }

        if (isset($args['active'])) {
            $query = $query->where('active', $args['active']);
        }

        if (isset($args['offset']) && isset($args['take'])) {
            $query = $query->offset($args['offset'])->take($args['take']);
        } else if (isset($args['offset']) && !isset($args['take'])) {
            $query = $query->offset($args['offset'])->take(10);
        }

        $workspaces = $query->get();
        return $workspaces;
    }

    public function get_workspace_details($rootValue, array $args)
    {
        $workspace_id = $args['id'];
        $user_id = request()->user()->id;

        $availed_free_plan_ids = [];

        RecentlySearchedWorkspace::updateOrCreate(['user_id' => $user_id, 'workspace_id' => $workspace_id], []);

        $subscriptions = Subscription::with('payment.plan')->whereHas('payment', function ($q) use ($workspace_id, $user_id) {
            $q->where('user_id', $user_id)->whereHas('plan', function ($qu) use ($workspace_id) {
                $qu->where('workspace_id', $workspace_id)->where('cost', 0);
            });
        })->get();

        for ($i = 0; $i < count($subscriptions); $i++) {
            $availed_free_plan_ids[] = $subscriptions[$i]->payment->plan->id;
        }

        $workspace = Workspace::with('amenities')->with('banners')->with('images')->with('plans')->with('weekly_schedules')->with('addresses')->find($workspace_id);

        $workspace['availedFreePlanIds'] = $availed_free_plan_ids;

        return $workspace;
    }

    public function get_paginated_workspaces_with_search($rootValue, array $args)
    {
        $id = $args['id'];
        $from = isset($args['offset']) ? $args['offset'] : 0;
        $size = isset($args['take']) ? $args['take'] : 10;
        $search =  isset($args['search']) ? $args['search'] : null;
        $filters = isset($args['filters']) && count($args['filters']) ? $args['filters'] : null;

        $workspace_type = WorkspaceType::find($id);

        if ($filters || $search) {

            $query = [
                'query' => [
                    'bool' => [
                        'must' => [
                            'bool' => [
                                'must' => [],
                                'should' => []
                            ]
                        ]
                    ]
                ]
            ];

            $active_query = $this->get_active_query();

            $query['query']['bool']['must']['bool']['must'][] = $active_query;

            if ($filters) {

                if (array_key_exists('propertyType', $filters)) {
                    $workspace_types = explode(',', $filters['propertyType']);

                    $should_query = $this->get_bool_should_query();

                    for ($i = 0; $i < count($workspace_types); $i++) {

                        $workspace_type_query = $this->get_filter_item_query($workspace_types[$i], "workspace_type.keyword");

                        $should_query->bool->should[] = $workspace_type_query;
                    }

                    $query['query']['bool']['must']['bool']['must'][] = $should_query;
                } else {
                    $should_query = $this->get_bool_should_query();
                    $workspace_type_query = $this->get_filter_item_query($workspace_type->name, "workspace_type.keyword");

                    $should_query->bool->should[] = $workspace_type_query;

                    $query['query']['bool']['must']['bool']['must'][] = $should_query;
                }

                if (array_key_exists('spaceType', $filters)) {
                    $space_types = explode(',', $filters['spaceType']);

                    $should_query = $this->get_bool_should_query();

                    for ($i = 0; $i < count($space_types); $i++) {
                        $space_type_query = $this->get_filter_item_query($space_types[$i], "space_types.keyword");

                        $should_query->bool->should[] = $space_type_query;

                        $query['query']['bool']['must']['bool']['must'][] = $should_query;
                    }
                }

                if (array_key_exists('amenities', $filters)) {
                    $amenities = explode(',', $filters['amenities']);

                    $should_query = $this->get_bool_should_query();

                    for ($i = 0; $i < count($amenities); $i++) {

                        $amenity_query = $this->get_filter_item_query($amenities[$i], "amenities.keyword");

                        $should_query->bool->should[] = $amenity_query;

                        $query['query']['bool']['must']['bool']['must'][] = $should_query;
                    }
                }

                if (array_key_exists('duration', $filters)) {
                    $duration_query = $this->get_filter_item_query($filters['duration'], "plan_types.keyword");

                    $query['query']['bool']['must']['bool']['must'][] = $duration_query;
                }

                if (array_key_exists('price', $filters)) {

                    $price_array = explode(" - ", $filters['price']);

                    $price_query = [
                        'range' => [
                            "per_day" => [
                                "gte" => $price_array[0],
                                "lte" => $price_array[1]
                            ]
                        ]
                    ];

                    $query['query']['bool']['must']['bool']['must'][] = $price_query;
                }

                if (array_key_exists('seatCapacity', $filters)) {
                    $seats_array = explode(" - ", $filters['seatCapacity']);

                    $seats_query = [
                        'range' => [
                            "seats" => [
                                "gte" => $seats_array[0],
                                "lte" => $seats_array[1]
                            ]
                        ]
                    ];

                    $query['query']['bool']['must']['bool']['must'][] = $seats_query;
                }

                if (array_key_exists('latLong', $filters) && $filters['latLong']) {
                    $distance_query = [
                        'distance_feature' => [
                            'field' => 'location',
                            'pivot' => '1000m',
                            'origin' => $filters['latLong']
                        ]
                    ];
                    $query['query']['bool']['must']['bool']['must'][] = $distance_query;
                }
            }

            if ($search) {
                if (!$filters) {
                    $should_query = $this->get_bool_should_query();
                    $workspace_type_query = $this->get_filter_item_query($workspace_type->name, "workspace_type.keyword");

                    $should_query->bool->should[] = $workspace_type_query;

                    $query['query']['bool']['must']['bool']['must'][] = $should_query;
                }

                $search_fields = [
                    ['search_in' => 'name', 'boost' => 10],
                    ['search_in' => 'address', 'boost' => 5],
                    ['search_in' => 'description', 'boost' => 3],
                    ['search_in' => 'workspace_type', 'boost' => 2]
                ];

                for ($i = 0; $i < count($search_fields); $i++) {
                    $search_query = $this->get_search_query($search_fields[$i], $search);

                    $query['query']['bool']['must']['bool']['should'][] = $search_query;
                }

                if (is_numeric($search)) {
                    $normal_query = $this->get_normal_query("per_day", $search);

                    $query['query']['bool']['must']['bool']['should'][] = $normal_query;

                    $normal_query = $this->get_normal_query("seats", $search);

                    $query['query']['bool']['must']['bool']['should'][] = $normal_query;
                } else {
                    $normal_query = $this->get_normal_query("workspace_type", $search);
                    $query['query']['bool']['must']['bool']['should'][] = $normal_query;

                    $normal_query = $this->get_normal_query("amenities", $search);
                    $query['query']['bool']['must']['bool']['should'][] = $normal_query;

                    $normal_query = $this->get_normal_query("duration", $search);
                    $query['query']['bool']['must']['bool']['should'][] = $normal_query;

                    $normal_query = $this->get_normal_query("space_type", $search);
                    $query['query']['bool']['must']['bool']['should'][] = $normal_query;
                }
            }

            $workspaces_raw = Workspace::searchRaw($query);
            $max_score = $workspaces_raw['hits']['max_score'];
            $count =  $workspaces_raw['hits']['total']['value'];
            $workspace_hits =  $workspaces_raw['hits']['hits'];
            $workspaces = [];
            for ($i = 0; $i < count($workspace_hits); $i++) {
                $workspace_score = $workspace_hits[$i]['_score'];
                $cut_off = $max_score - ($max_score * 0.3);
                if ($workspace_score > $cut_off) {
                    $workspace_details = $workspace_hits[$i]['_source'];
                    $workspaces[] = $workspace_details;
                }
            }
        } else {
            $count = Workspace::count();
            $workspaces_without_todays_schedule = Workspace::with('date_schedules', 'weekly_schedules', 'plans', 'images')->where('workspace_type_id', $id)->where('active', 1)->offset($from)->limit($size)->get();
            $workspaces_with_schedule = $this->get_date_schedule($workspaces_without_todays_schedule);
            $workspaces_without_image_urls = $this->get_per_day($workspaces_with_schedule);
            $workspaces = $this->get_image_urls($workspaces_without_image_urls);
        }

        return ['count' => $count, 'type' => $workspace_type, 'workspaces' => $workspaces];
    }

    public function get_user_workspaces($rootValue, array $args)
    {
        return request()->user()->workspaces;
    }

    public function get_recently_searched_workspaces($rootValue, array $args)
    {
        $recently_searched_workspaces = RecentlySearchedWorkspace::with('workspace.addresses')->with('workspace.images')->where('user_id', request()->user()->id)->orderBy('updated_at', 'desc')->get();
        return $recently_searched_workspaces;
    }

    private function get_active_query()
    {
        $active_query = new \stdClass();
        $active_query->match = new \stdClass();
        $active_query->match->active = 1;

        return $active_query;
    }

    private function get_bool_should_query()
    {
        $should_query = new \stdClass();
        $should_query->bool = new \stdClass();
        $should_query->bool->should = [];

        return $should_query;
    }

    private function get_filter_item_query($item, $key)
    {
        $workspace_type_query = new \stdClass();
        $workspace_type_query->match = new \stdClass();
        $workspace_type_query->match->{$key} = $item;

        return $workspace_type_query;
    }

    private function get_date_schedule($workspaces_without_todays_schedule)
    {
        $today = Carbon::today();

        $workspaces = [];
        for ($i = 0; $i < count($workspaces_without_todays_schedule); $i++) {
            $todays_schedule = null;
            if (count($workspaces_without_todays_schedule[$i]->date_schedules)) {
                $todays_schedule = $workspaces_without_todays_schedule[$i]->date_schedules()->where('date', $today)->first();
            }

            if (!$todays_schedule) {
                $today = Carbon::now()->format('l');
                $todays_schedule = $workspaces_without_todays_schedule[$i]->weekly_schedules()->where('day', $today)->first();
            }

            $workspaces_without_todays_schedule[$i]['opens_at'] = $todays_schedule->opens_at;
            $workspaces_without_todays_schedule[$i]['closes_at'] = $todays_schedule->closes_at;

            $workspaces[] = $workspaces_without_todays_schedule[$i];
        }
        return $workspaces;
    }


    private function get_search_query($field, $search)
    {
        return [
            'match' => [
                $field['search_in'] => [
                    "query" => $search,
                    "boost" => $field['boost']
                ]
            ]
        ];
    }

    private function get_normal_query($item, $search)
    {
        return [
            'match' => [
                $item => [
                    "query" => $search
                ]
            ]
        ];
    }

    private function get_per_day($workspaces_without_plans)
    {
        $workspaces = [];
        for ($i = 0; $i < count($workspaces_without_plans); $i++) {
            $lowest_cost = $workspaces_without_plans[$i]->plans->min('cost');
            $workspaces_without_plans[$i]['per_day'] = $lowest_cost;
            $workspaces[] = $workspaces_without_plans[$i];
        }
        return $workspaces;
    }

    private function get_image_urls($workspaces_without_image_urls)
    {
        $workspaces = [];
        for ($i = 0; $i < count($workspaces_without_image_urls); $i++) {
            $image_urls = $workspaces_without_image_urls[$i]->images->pluck('image_url');
            $workspaces_without_image_urls[$i]['image_urls'] = $image_urls;
            $workspaces[] = $workspaces_without_image_urls[$i];
        }
        return $workspaces;
    }

    // private function get_schedule($workspaces_without_todays_schedule)
    // {
    //     $today = Carbon::today();

    //     $workspaces = [];
    //     for ($i = 0; $i < count($workspaces_without_todays_schedule); $i++) {
    //         $todays_schedule = null;
    //         if (count($workspaces_without_todays_schedule[$i]->date_schedules)) {
    //             $todays_schedule = $workspaces_without_todays_schedule[$i]->date_schedules()->where('date', $today)->first();
    //         }

    //         if (!$todays_schedule) {
    //             $today = Carbon::now()->format('l');
    //             $todays_schedule = $workspaces_without_todays_schedule[$i]->weekly_schedules()->where('day', $today)->first();
    //         }

    //         $workspaces_without_todays_schedule[$i]['opens_at'] = $todays_schedule->opens_at;
    //         $workspaces_without_todays_schedule[$i]['closes_at'] = $todays_schedule->closes_at;

    //         $workspaces[] = $workspaces_without_todays_schedule[$i];
    //     }
    //     return $workspaces;
    // }

}
