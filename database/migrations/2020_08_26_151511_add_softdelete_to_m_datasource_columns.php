<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftdeleteToMDatasourceColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_datasource_columns', function (Blueprint $table) {
            $table->softDeletes();
            DB::statement('ALTER TABLE m_datasource_columns DROP INDEX m_data_column_mappings_table_definition_id_datasource_id_unique');
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
            DB::statement('ALTER TABLE m_datasource_columns ADD UNIQUE INDEX m_data_column_mappings_table_definition_id_datasource_id_unique (table_column_id, datasource_id)');
            $table->dropSoftDeletes();
        });
    }
}
