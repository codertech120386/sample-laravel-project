<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Faker\Generator as Faker;
use stdClass;

use App\Workspace;
use App\WorkspacePlan;
use App\Amenity;
use App\WorkspaceType;
use App\WeeklySchedule;
use App\UserToken;
use App\Subscription;
use App\RecentlySearchedWorkspace;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function show(Workspace $workspace)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function edit(Workspace $workspace)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Workspace $workspace)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function destroy(Workspace $workspace)
    {
        //
    }

    public function seed(Faker $faker)
    {
        // seeding workspace types
        $workspace_types = ["coworking space", "cafe", "pub", "hotel", "shared_office", "business centre", "training facility"];

        for ($i = 0; $i < count($workspace_types); $i++) {
            $workspace_type = new WorkspaceType();
            $workspace_type->name = $workspace_types[$i];

            $workspace_type->save();
        }

        //seeding amenities
        for ($i = 1; $i <= 50; $i++) {
            $amenity = new Amenity();
            $amenity->id = $i;
            $amenity->name = $faker->sentence($nbWords = 2);
            $amenity->icon_url = $faker->url;

            $amenity->save();
        }

        // seeding workspaces
        for ($i = 0; $i < 100; $i++) {
            $workspace = new Workspace();
            $workspace->id = $i + 1;
            $workspace->name = $faker->sentence($nbWords = 3);
            $workspace->description = $faker->paragraph();
            $workspace->address = $faker->paragraph();
            $workspace->active = $faker->boolean();
            $workspace->seats = $faker->numberBetween($min = 1, $max = 10);
            $workspace->profile_image = "https://coffic-images.s3.ap-south-1.amazonaws.com/photo-1554118811-1e0d58224f24.jpg";
            $workspace->location_id = $faker->word();
            $workspace->workspace_type_id = $faker->numberBetween(1, 4);

            // seeding workspace plans
            for ($j = 1; $j <= 9; $j++) {
                $plan = new WorkspacePlan();
                $plan->workspace_id = $i + 1;
                if ($j == 1 || $j == 4 || $j == 7) {
                    $plan->id = $i * 9 + $j;
                    $plan->duration = 1;
                    $plan->title = "Daily";
                    $plan->sub_title = "1 day pass";
                    if ($j == 1) {
                        $plan->space_type = "Open Desk";
                        $plan->cost = $faker->randomElement([200, 300, 400]);
                    } else if ($j == 4) {
                        $plan->space_type = "Private Cabin";
                        $plan->cost = $faker->randomElement([800, 900, 1000]);
                    } else {
                        $plan->space_type = "Meeting Room";
                        $plan->cost = $faker->randomElement([500, 600, 700]);
                    }
                } else if ($j == 2 || $j == 5 || $j == 8) {
                    $plan->duration = 7;
                    $plan->title = "Weekly";
                    $plan->sub_title = "7 day pass";
                    if ($j == 2) {
                        $plan->space_type = "Open Desk";
                        $plan->cost = $faker->randomElement([1000, 1500, 2000]);
                    } else if ($j == 5) {
                        $plan->space_type = "Private Cabin";
                        $plan->cost = $faker->randomElement([4000, 4500, 5000]);
                    } else {
                        $plan->space_type = "Meeting Room";
                        $plan->cost = $faker->randomElement([2500, 3000, 3500]);
                    }
                } else if ($j == 3 || $j == 6 || $j == 9) {
                    $plan->duration = 30;
                    $plan->title = "Monthly";
                    $plan->sub_title = "30 day pass";
                    if ($j == 3) {
                        $plan->space_type = "Open Desk";
                        $plan->cost = $faker->randomElement([4000, 6000, 8000]);
                    } else if ($j == 6) {
                        $plan->space_type = "Private Cabin";
                        $plan->cost = $faker->randomElement([16000, 18000, 20000]);
                    } else {
                        $plan->space_type = "Meeting Room";
                        $plan->cost = $faker->randomElement([10000, 12000, 14000]);
                    }
                }
                $plan->save();
            }

            // Seeding weekly schedules
            $days = [0 => 'monday', 1 => 'tuesday', 2 => 'wednesday', 3 => 'thursday', 4 => 'friday', 5 => 'saturday', 6 => 'sunday'];

            $opens_at = $faker->randomElement(['08:00', '09:00', '10:00', '11:00']);
            $closes_at = $faker->randomElement(['20:00', '21:00', '22:00', '22:30']);

            for ($k = 0; $k < 7; $k++) {
                $weekly_schedule = new WeeklySchedule();
                $weekly_schedule->workspace_id = $i + 1;
                $weekly_schedule->day = $days[$k];
                $weekly_schedule->opens_at = $opens_at;
                $weekly_schedule->closes_at = $closes_at;

                if ($k == 5 || $k == 6) {
                    $weekly_schedule->opens_at = null;
                    $weekly_schedule->closes_at = null;
                }
                $weekly_schedule->save();
            }

            // seeding workspace amenities
            $numbers = range(1, 50);
            shuffle($numbers);

            for ($l = 0; $l < 15; $l++) {
                \DB::table('amenity_workspace')->insert(
                    ['amenity_id' => $numbers[$l], 'workspace_id' => $workspace->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
                );
            }

            // re-saving the workspace to update index
            $workspace->save();
        }
        return "done";
    }

    public function search(Request $request)
    {
        $id = $request->get('id');
        $from = $request->has('offset') ? $request->get('offset') : 0;
        $size = $request->has('take') ? $request->get('take') : 10;
        $search =  $request->has('search') ? $request->get('search') : null;
        $filters = $request->has('filters') ? $request->get('filters') : null;

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
                        $space_type_query = $this->get_filter_item_query($space_types[$i], "space_type.keyword");

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

                if (array_key_exists('latLong', $filters)) {
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

            return $query;
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
            $workspaces_without_todays_schedule = Workspace::with('date_schedules', 'weekly_schedules', 'plans', 'images')->where('workspace_type_id', $id)->where('active', 1)->offset($from)->limit($size)->get();

            $workspaces_with_schedule = $this->get_date_schedule($workspaces_without_todays_schedule);
            $workspaces_without_image_urls = $this->get_per_day($workspaces_with_schedule);
            $workspaces_without_address = $this->get_image_urls($workspaces_without_image_urls);
            $workspaces = $this->get_address($workspaces_without_address);
            $count = count($workspaces);
        }

        return ['count' => $count, 'type' => $workspace_type, 'workspaces' => $workspaces];
    }

    public function get_workspace(Request $request)
    {
        $user_token = UserToken::with('user.workspaces.images')->where('token', $request->get('userToken'))->first();

        $workspaces = $user_token->user->workspaces;

        return $workspaces;
    }

    public function get_workspace_details(Request $request)
    {
        $workspace_id = $request->get('id');
        $token = $request->get('token');

        $availed_free_plan_ids = [];

        $user_id = request()->user()->id;

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
            if ($workspaces_without_image_urls[$i]->images->count()) {

                $image_urls = $workspaces_without_image_urls[$i]->images->pluck('image_url');
                $workspaces_without_image_urls[$i]['image_urls'] = $image_urls;
            } else {
                $workspaces_without_image_urls[$i]['image_urls'] = [];
            }
            $workspaces[] = $workspaces_without_image_urls[$i];
        }
        return $workspaces;
    }

    private function get_address($workspaces_without_address)
    {
        $workspaces = [];
        for ($i = 0; $i < count($workspaces_without_address); $i++) {
            $address = $workspaces_without_address[$i]->addresses->first();
            $workspaces_without_address[$i]['address'] = $address->address;
            $workspaces_without_address[$i]['short_address'] = $address->short_address;
            $workspaces[] = $workspaces_without_address[$i];
        }
        return $workspaces;
    }
}
