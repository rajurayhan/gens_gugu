<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DatasourceColumns extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $table = 'm_datasource_columns';

    public function dataSource()
    {
        return $this->belongsTo(Datasource::class, 'datasource_id', 'id');
    }

    public function tableDefinition()
    {
        return $this->belongsTo(TableColumns::class, 'table_column_id', 'id');
    }

    public function tables()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
}
