<?php

namespace App\GraphQL\Mutations;

use App\User;
use App\UserProfessionalDetails;
use App\Exceptions\AuthenticationException;

class UserDetailsModel
{
    public function update_professional_details($rootValue, array $args)
    {
        $designation = $args['designation'] ?? null;
        $organisation = $args['organization'] ?? null;
        $industry = $args['industry'] ?? null;
        $education = $args['education'] ?? null;
        $description = $args['description'] ?? null;

        try {
            return UserProfessionalDetails::updateOrCreate(['user_id' => request()->user()->id], [
                'designation' => $designation, 'organization' => $organisation, 'industry' => $industry, 'education' => $education, 'description' => $description
            ]);
        } catch (AuthenticationException $ex) {
            throw $ex(' Something went wrong .. Please try again later ..', 'System issue', 'toast');
        }
    }

    public function update_personal_details($rootValue, array $args)
    {
        try {
            return User::updateOrCreate(['id' => request()->user()->id], [
                'name' => $args['name'],
                'gender' => $args['gender'],
                'phone' => $args['phone']
            ]);
        } catch (AuthenticationException $ex) {
            throw $ex(' Something went wrong .. Please try again later ..', 'System issue', 'toast');
        }
    }

    // public function upload_profile_image($rootValue, array $args)
    // {
    //     $user_token = UserToken::get_user_token_with_user($args['userToken']);

    //     if ($user_token) {

    //     }
    // }
}
