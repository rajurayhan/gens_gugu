<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    protected $fillable = [ 'value' ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'setting_user', 'setting_id', 'user_id')
            ->withPivot('value');
    }
}
