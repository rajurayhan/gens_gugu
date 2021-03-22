<?php

namespace App\Models;

use App\Models\Datasource;
use App\Models\TableColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    
    protected $table = 'm_tables';

    public function definitions()
    {
        return $this->hasMany(TableColumns::class);
    }

    public function dataSource()
    {
        return $this->hasMany(Datasource::class);
    }
}
