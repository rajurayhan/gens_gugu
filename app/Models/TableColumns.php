<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Table;

class TableColumns extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $table = 'm_table_columns';

    public function tables()
    {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }
}
