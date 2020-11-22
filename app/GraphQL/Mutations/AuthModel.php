<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\AuthenticationException;
use App\Mail\ForgotPassword;
use App\Mail\VerifyEmail;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthModel {

    public function signup($rootValue, array $args)
    {
        if (User::where("email", $args['email'])->first())
        {
            throw new AuthenticationException('User already exists', 'email id is already taken', 'email');
        }
        if (User::where("phone", $args['phone'])->first())
        {
            throw new AuthenticationException('User already exists', 'phone number is already taken', 'phone');
        }
        $user = User::create($args);
        $user->create_and_persist_reset_code();
        Mail::to($args['email'])->send(new VerifyEmail($user));

        return $user;
    }

    public function login($rootValue, array $args)
    {
        $email = $args['email'];
        if (Auth::validate(['email' => $email, 'password' => $args['password']]))
        {
            $user = User::fetch_user_from_email($email);

            return $this->create_and_persist_user_token($user);
        }
        throw new AuthenticationException('Invalid Username or password', 'Invalid Username or password', 'toast');
    }

    private function create_and_persist_user_token($user)
    {
        // Creating a token with Laravel Sanctum
        $user['token'] = $user->createToken('auth')->plainTextToken;

        return $user;
    }

    public function forgot_password($rootValue, array $args)
    {
        $user = User::fetch_user_from_email($args['email']);

        if ($user)
        {

            $user->create_and_persist_reset_code();

            Mail::to($args['email'])->send(new ForgotPassword($user));

            return ["message" => "Mail sent .. Please check your inbox", "success" => true];
        } else
        {
            return ["message" => "Something went wrong Please try again", "error" => true];
        }
    }

    public function change_password($rootValue, array $args)
    {
        $user = User::fetch_user_from_reset_code($args['code']);

        if ($user)
        {
            $user->create_and_persist_user_password($args['password']);
            $user->delete_reset_code();

            return $this->create_and_persist_user_token($user);
        } else
        {
            throw new AuthenticationException('Invalid code .. Please check and try again', 'Invalid code', 'toast');
        }
    }

    public function verify_email($rootValue, array $args)
    {
        $user = User::fetch_user_from_reset_code($args['code']);

        if ($user)
        {
            $user->update_email_verified();
            return $this->create_and_persist_user_token($user);
        } else
        {
            throw new AuthenticationException('Please try again with the link in email', 'Invalid code or email', 'toast');
        }
    }
}
