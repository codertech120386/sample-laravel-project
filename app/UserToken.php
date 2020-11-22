<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public static function get_user_token_with_user($token)
    {
        return UserToken::with('user')->where('token', $token)->first();
    }
}
