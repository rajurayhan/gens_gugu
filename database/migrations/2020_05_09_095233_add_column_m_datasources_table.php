<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMDatasourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_datasources', function (Blueprint $table) {
            $table->bigInteger('table_id')->unsigned()->nullable()->after('datasource_name');
            $table->integer('starting_row_number')->nullable()->after('table_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_datasources', function (Blueprint $table) {
            $table->dropColumn('table_id');
            $table->dropColumn('starting_row_number');
        });
    }
}
