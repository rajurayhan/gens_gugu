<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TestMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::dropIfExists('xls_test_all_types');
        Schema::create('xls_test_all_types', function (Blueprint $table) {
            $table->string('file_name');
            $table->bigInteger('file_id');
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->bigInteger('test_column_bigint')->charset(null)->nullable()->default(null);
            $table->date('test_column_date')->charset(null)->nullable()->default(null);
            $table->string('test_column_varchar', 255)->nullable()->default(null);
            $table->decimal('test_column_decimal', 8, 2)->charset(null)->nullable()->default(null);
            $table->dateTime('test_column_datetime', 0)->charset(null)->nullable()->default(null);
        });
        Schema::dropIfExists('xls_test_all_types_with_validation');
        Schema::create('xls_test_all_types_with_validation', function (Blueprint $table) {
            $table->string('file_name');
            $table->bigInteger('file_id');
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->bigInteger('test_column_bigint')->charset(null)->nullable()->default(null);
            $table->date('test_column_date')->charset(null)->nullable()->default(null);
            $table->string('test_column_varchar', 255)->nullable()->default(null);
            $table->decimal('test_column_decimal', 8, 2)->charset(null)->nullable()->default(null);
            $table->dateTime('test_column_datetime', 0)->charset(null)->nullable()->default(null);
        });
        
        \DB::table('xls_test_all_types')->delete();
        \DB::table('xls_test_all_types_with_validation')->delete();


        \DB::table('m_tables')->delete();
        \DB::table('m_tables')->insert([
            [
                'id' => 1,
                'table_name' => 'xls_test_all_types',
                'table_name_alias' => 'テスト用_全カラムタイプ',
                'created_by' => null,
                'created_at' => '2020-07-29 14:20:29',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:20:29',
            ],
            [
                'id' => 10,
                'table_name' => 'xls_test_all_types_with_validation',
                'table_name_alias' => 'テスト用_全カラムタイプ（Validationあり）',
                'created_by' => null,
                'created_at' => '2020-07-29 14:20:29',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:20:29',
            ],
        ]);



        \DB::table('m_table_columns')->delete();
        \DB::table('m_table_columns')->insert([
            //xls_test_all_types
            [
                'id' => 1,
                'table_id' => 1,
                'column_name' => 'test_column_bigint',
                'column_name_alias' => 'カラム_BIGINT',
                'data_type' => 'bigint',
                'length' => 10,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => 2,
                'table_id' => 1,
                'column_name' => 'test_column_date',
                'column_name_alias' => 'カラム_DATE',
                'data_type' => 'date',
                'length' => null,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => 3,
                'table_id' => 1,
                'column_name' => 'test_column_datetime',
                'column_name_alias' => 'カラム_DATETIME',
                'data_type' => 'datetime',
                'length' => null,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => 4,
                'table_id' => 1,
                'column_name' => 'test_column_decimal',
                'column_name_alias' => 'カラム_DECIMAL',
                'data_type' => 'decimal',
                'length' => null,
                'maximum_number' => 8,
                'decimal_part' => 2,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => 5,
                'table_id' => 1,
                'column_name' => 'test_column_varchar',
                'column_name_alias' => 'カラム_文字列',
                'data_type' => 'varchar',
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],

            //xls_test_all_types_with_validation
            [
                'id' => 11,
                'table_id' => 10,
                'column_name' => 'test_column_bigint',
                'column_name_alias' => 'カラム_BIGINT',
                'data_type' => 'bigint',
                'length' => 10,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => 'integer|min:1',    //VALIDATION
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => 12,
                'table_id' => 10,
                'column_name' => 'test_column_date',
                'column_name_alias' => 'カラム_DATE',
                'data_type' => 'date',
                'length' => null,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => 13,
                'table_id' => 10,
                'column_name' => 'test_column_datetime',
                'column_name_alias' => 'カラム_DATETIME',
                'data_type' => 'datetime',
                'length' => null,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => 14,
                'table_id' => 10,
                'column_name' => 'test_column_decimal',
                'column_name_alias' => 'カラム_DECIMAL',
                'data_type' => 'decimal',
                'length' => null,
                'maximum_number' => 8,
                'decimal_part' => 2,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => 15,
                'table_id' => 10,
                'column_name' => 'test_column_varchar',
                'column_name_alias' => 'カラム_文字列',
                'data_type' => 'varchar',
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => 'required',    //VALIDATION
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
            ],
        ]);



        \DB::table('m_datasources')->delete();
        \DB::table('m_datasources')->insert([
            [
                'id' => 1,
                'datasource_name' => 'テストエクセル',
                'table_id' => 1,
                'starting_row_number' => 2,
                'created_by' => null,
                'created_at' => '2020-07-29 14:30:07',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:30:07',
            ],
            [
                'id' => 10,
                'datasource_name' => 'テストエクセル(strict)',
                'table_id' => 10,
                'starting_row_number' => 2,
                'created_by' => null,
                'created_at' => '2020-07-29 14:30:07',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:30:07',
            ],
        ]);




        \DB::table('m_datasource_columns')->delete();
        \DB::table('m_datasource_columns')->insert([
            //テストエクセル
            [
                'id' => 1,
                'table_column_id' => 1,
                'datasource_id' => 1,
                'datasource_column_number' => 1,
                'datasource_column_name' => '列A_BIGINT',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => 2,
                'table_column_id' => 2,
                'datasource_id' => 1,
                'datasource_column_number' => 2,
                'datasource_column_name' => '列B_DATE',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => 3,
                'table_column_id' => 3,
                'datasource_id' => 1,
                'datasource_column_number' => 3,
                'datasource_column_name' => '列C_DATETIME',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => 4,
                'table_column_id' => 4,
                'datasource_id' => 1,
                'datasource_column_number' => 4,
                'datasource_column_name' => '列D_DECIMAL',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => 5,
                'table_column_id' => 5,
                'datasource_id' => 1,
                'datasource_column_number' => 5,
                'datasource_column_name' => '列E_VARCHAR',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],


            //テストエクセル(strict)
            [
                'id' => 11,
                'table_column_id' => 11,
                'datasource_id' => 10,
                'datasource_column_number' => 1,
                'datasource_column_name' => '列A_BIGINT',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => 12,
                'table_column_id' => 12,
                'datasource_id' => 10,
                'datasource_column_number' => 2,
                'datasource_column_name' => '列B_DATE',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => 13,
                'table_column_id' => 13,
                'datasource_id' => 10,
                'datasource_column_number' => 3,
                'datasource_column_name' => '列C_DATETIME',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => 14,
                'table_column_id' => 14,
                'datasource_id' => 10,
                'datasource_column_number' => 4,
                'datasource_column_name' => '列D_DECIMAL',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => 15,
                'table_column_id' => 15,
                'datasource_id' => 10,
                'datasource_column_number' => 5,
                'datasource_column_name' => '列E_VARCHAR',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
        ]);
    }
}
