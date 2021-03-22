<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnMDatasourceColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_datasource_columns', function (Blueprint $table) {
            $table->renameColumn('table_definition_id', 'table_column_id');
            $table->renameColumn('column_number', 'datasource_column_number');
            $table->renameColumn('column_name', 'datasource_column_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_datasource_columns', function (Blueprint $table) {
            $table->renameColumn('table_column_id', 'table_definition_id');
            $table->renameColumn('datasource_column_number', 'column_number');
            $table->renameColumn('datasource_column_name', 'column_name');
        });
    }
}
