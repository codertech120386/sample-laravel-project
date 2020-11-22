<?php

namespace App\GraphQL\Queries;

use App\UserProfessionalDetails;

class UserDetailsModel
{
    public function get_user_professional_details($rootValue, array $args)
    {
        return UserProfessionalDetails::with('user')->where('user_id', request()->user()->id)->first();
    }
}
