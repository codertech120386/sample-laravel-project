<?php

namespace App\GraphQL\Queries;

use App\Subscription;

use App\Workspace;
use App\UserToken;
use App\Exceptions\AuthenticationException;
use App\RecentlySearchedWorkspace;
use Carbon\Carbon;

class V1WorkspaceModel
{
    public function get_workspace_details($rootValue, array $args)
    {
        $workspace_id = $args['id'];

        $availed_free_plan_ids = [];
        $is_subscribed = false;
        $user_id = request()->user()->id;

        RecentlySearchedWorkspace::updateOrCreate(['user_id' => $user_id, 'workspace_id' => $workspace_id], []);

        $free_subscriptions = Subscription::with('payment.plan')->whereHas('payment', function ($q) use ($workspace_id, $user_id) {
            $q->where('user_id', $user_id)->whereHas('plan', function ($qu) use ($workspace_id) {
                $qu->where('workspace_id', $workspace_id)->where('cost', 0);
            });
        })->get();

        for ($i = 0; $i < count($free_subscriptions); $i++) {
            $availed_free_plan_ids[] = $free_subscriptions[$i]->payment->plan->id;
        }

        $paid_subscription = Subscription::with('payment.plan')->where('status', 'confirmed')->whereDate('start_date', '<=', Carbon::now())->whereDate('end_date', '>=', Carbon::now())->whereHas('payment', function ($q) use ($workspace_id, $user_id) {
            $q->where('user_id', $user_id)->whereHas('plan', function ($qu) use ($workspace_id) {
                $qu->where('workspace_id', $workspace_id)->where('cost', '>', 0);
            });
        })->first();

        if ($paid_subscription) {
            $is_subscribed = true;
        }

        $workspace = Workspace::with('amenities')->with('banners')->with('images')->with('plans')->with('weekly_schedules')->with('addresses')->find($workspace_id);

        $workspace['availedFreePlanIds'] = $availed_free_plan_ids;
        $workspace['isSubscribed'] = $is_subscribed;

        return $workspace;
    }
}
