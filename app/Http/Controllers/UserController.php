<?php

namespace App\Http\Controllers;

use App\User;
use App\UserToken;
use App\Exceptions\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function signup(Request $request)
    {
        if (User::where("email", $request->email)->first()) {
            return response()->json(["error" => "email taken"]);
            // throw new AuthenticationException('User already exists', 'email id is already taken', 'email');
        }
        if (User::where("phone", $request->phone)->first()) {
            return response()->json(["error" => "phone taken"]);
            // throw new AuthenticationException('User already exists', 'phone number is already taken', 'phone');
        }
        $user = User::create($request->all());
        return $user;
    }

    public function login(Request $request)
    {
        $email = $request->get('email');
        if (Auth::validate(['email' => $email, 'password' => $request->get('password')])) {
            $user = User::fetch_user_from_email($email);

            return $this->create_and_persist_user_token($user);
        }
        throw new AuthenticationException('Invalid Username or password', 'Invalid Username or password', 'toast');
    }

    public function get_user(Request $request)
    {
        $user_token = UserToken::get_user_token_with_user($request->get('userToken'));

        return $user_token;

        if ($user_token) {
            return User::find(1);
        }
        throw new AuthenticationException('User already exists', 'email id is already taken', 'email');
    }
}
