<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftdeleteToMTableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'm_table_columns',
            function (Blueprint $table) {
                $table->softDeletes();
                DB::statement('ALTER TABLE m_table_columns DROP INDEX m_table_definitions_table_id_column_name_unique');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'm_table_columns',
            function (Blueprint $table) {
                DB::statement('ALTER TABLE m_table_columns ADD UNIQUE INDEX m_table_definitions_table_id_column_name_unique (table_id, column_name)');
                $table->dropSoftDeletes();
            }
        );
    }
}
