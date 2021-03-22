<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Material;
use App\Models\HiikuSeiseki;

class File extends Model
{

    use SoftDeletes;
    protected $guarded = [];

}
