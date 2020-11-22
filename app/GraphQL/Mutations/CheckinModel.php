<?php

namespace App\GraphQL\Mutations;

use Carbon\Carbon;

use App\Checkin;

class CheckinModel
{
    public function checkin($rootValue, array $args)
    {
        $user_id = request()->user()->id;

        $checkin = Checkin::where('user_id', $user_id)->where('workspace_id', $args['workspaceId'])->where('checked_in_date', Carbon::now()->toDateString())->first();

        if (!$checkin) {
            return Checkin::create([
                'user_id' => $user_id,
                'workspace_id' => $args['workspaceId'],
                'checked_in_date' => Carbon::now()
            ], []);
        }
        return $checkin;
    }
}
