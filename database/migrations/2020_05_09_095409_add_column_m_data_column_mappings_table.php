<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMDataColumnMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_data_column_mappings', function (Blueprint $table) {
            $table->string('column_name')->nullable()->after('column_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_data_column_mappings', function (Blueprint $table) {
            $table->dropColumn('column_name');
        });
    }
}
