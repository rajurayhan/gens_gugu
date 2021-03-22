<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMDataColumnMappings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_data_column_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('table_definition_id')->unsigned();
            $table->bigInteger('datasource_id')->unsigned();
            $table->integer('column_number');

            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();

            // 設定
            $table->unique(['table_definition_id', 'datasource_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_data_column_mappings');
    }
}
