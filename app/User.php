<?php

namespace App;

use App\Models\Setting;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

//use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{

    /* use EntrustUserTrait, Notifiable, SoftDeletes {
        SoftDeletes::restore insteadof EntrustUserTrait;
        EntrustUserTrait::restore insteadof SoftDeletes;
    } */

    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_admin',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_admin' => 'boolean',
        'is_first_login' => 'boolean',
    ];

    public function getMetaAttribute()
    {
        $meta = (object) [];
        return $meta;
    }

    public function settings()
    {
        return $this->belongsToMany(Setting::class, 'setting_user', 'user_id', 'setting_id')
            ->withPivot('value');
    }
}
