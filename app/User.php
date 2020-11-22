<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Payment;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'gender'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function receivesBroadcastNotificationsOn()
    {
        return 'Notifications.' . $this->id;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function workspaces()
    {
        return $this->belongsToMany('App\Workspace', 'recently_searched_workspaces')
            ->using('App\RecentlySearchedWorkspace');
    }

    public function professional_details()
    {
        return $this->hasOne(UserProfessionalDetails::class);
    }

    public static function fetch_user_from_reset_code($reset_code)
    {
        return User::where('reset_code', $reset_code)->first();
    }

    public static function fetch_user_from_email($email)
    {
        return User::whereEmail($email)->first();
    }

    public function create_and_persist_reset_code()
    {
        $code = Str::random(50);
        $this->reset_code = $code;
        $this->save();
    }

    public function delete_reset_code()
    {
        $this->reset_code = null;
        $this->save();
    }

    public function create_and_persist_user_password($password)
    {
        $password = bcrypt($password);
        $this->password = $password;
        $this->save();
    }

    public function update_email_verified()
    {
        $this->email_verified_at = Carbon::now();
        $this->save();
    }
}
