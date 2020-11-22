<?php

namespace App\GraphQL\Queries;

use App\Checkin;

use Carbon\Carbon;

class CheckinModel
{

    public function get_user_checkin_history($rootValue, array $args)
    {
        $user_id = request()->user()->id;

        if ($args['reverse']) {
            return Checkin::with('user')->with('workspace')->where('user_id', $user_id)
            ->whereDate('checked_in_date', '>=', $args['startDate'])->whereDate('checked_in_date', '<=', $args['endDate'])
            ->orderBy('checked_in_date', 'desc')->limit($args['limit'])->get();
        }
        return Checkin::with('user')->with('workspace')->where('user_id', $user_id)
        ->whereDate('checked_in_date', '>=', $args['startDate'])->whereDate('checked_in_date', '<=', $args['endDate'])
        ->limit($args['limit'])->get();
    }

    public function get_user_checkin_dates($rootValue, array $args)
    {
        $user_id = request()->user()->id;
        $start_date = Carbon::createFromFormat("Y-m-d", $args["startDate"])->firstOfMonth()->toDateString();
        $end_date = Carbon::createFromFormat("Y-m-d", $args["startDate"])->lastOfMonth()->toDateString();

        return Checkin::with('user')->with('workspace')->where('user_id', $user_id)
        ->whereDate('checked_in_date', '>=', $start_date)->whereDate('checked_in_date', '<=', $end_date)->limit($args['limit'])->get();

        
    }
}
