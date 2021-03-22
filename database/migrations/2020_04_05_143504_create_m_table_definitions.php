<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMTableDefinitions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_table_definitions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('table_id')->unsigned();
            $table->string('column_name');
            $table->string('column_name_alias');
            $table->string('data_type');
            $table->integer('length')->nullable();
            $table->integer('maximum_number')->nullable();
            $table->integer('decimal_part')->nullable();
            $table->string('validation')->nullable();

            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();

            // 設定
            $table->unique(['table_id', 'column_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_table_definitions');
    }
}
