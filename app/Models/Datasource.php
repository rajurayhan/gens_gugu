<?php

namespace App\Models;

use App\Models\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Datasource extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $table = 'm_datasources';

    public function tables()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function datasourceColumns()
    {
        return $this->hasMany(DatasourceColumns::class);
    }
}
