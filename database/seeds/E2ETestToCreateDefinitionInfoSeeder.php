<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class E2ETestToCreateDefinitionInfoSeeder extends Seeder
{
    private static $mTables = 'm_tables';
    private static $mTableColumns = 'm_table_columns';
    private static $mDatasources = 'm_datasources';
    private static $mDatasourceColumns = 'm_datasource_columns';

    // テスト用に作成するテーブル名
    // このテーブル名を基にmasterテーブルから既存のテストデータ削除を行うため基本的に変更不可
    // 変更する場合は、変更前のテストデータを削除してから変更すること
    private static $rawData1 = 'xls_for_e2e_test';
    private static $table_without_data_and_column = 'table_without_data_column_e2e_test';
    private static $table_without_data = 'table_without_data_e2e_test';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 前回E2ETestSeederを実行した時に生成したデータを削除
        $this->deleteOldTargetData(static::$rawData1);
        $this->deleteOldTargetData(static::$table_without_data_and_column);
        $this->deleteOldTargetData(static::$table_without_data);
        // 新しくデータ生成
        $this->createNewData();
        $this->createNewTableWithoutData();
        $this->createNewTableWithoutColumnAndData();
        
    }

    private function deleteOldTargetData($targetData)
    {
        Schema::dropIfExists($targetData);

        $oldMtable = \DB::table(static::$mTables)->where('table_name', $targetData)->first();
        if ($oldMtable == null) {
            return;
        }

        $oldMtableId = $oldMtable->id;
        \DB::table(static::$mTables)->where('table_name', $targetData)->delete();
        \DB::table(static::$mTableColumns)->where('table_id', $oldMtableId)->delete();

        $oldMDatasource = \DB::table(static::$mDatasources)->where('table_id', $oldMtableId)->first();
        if ($oldMDatasource == null) {
            return;
        }

        $oldMDatasourceId = $oldMDatasource->id;
        $oldMDatasource = \DB::table(static::$mDatasources)->where('table_id', $oldMtableId)->delete();
        \DB::table(static::$mDatasourceColumns)->where('datasource_id', $oldMDatasourceId)->delete();
    }

    private function createNewData()
    {
        Schema::create(static::$rawData1, function (Blueprint $table) {
            $table->string('file_name');
            $table->bigInteger('file_id');
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->bigInteger('test_column_bigint')->charset(null)->nullable()->default(null);
            $table->date('test_column_date')->charset(null)->nullable()->default(null);
            $table->string('test_column_varchar', 255)->nullable()->default(null);
            $table->decimal('test_column_decimal', 65, 30)->charset(null)->nullable()->default(null);
            $table->dateTime('test_column_datetime', 0)->charset(null)->nullable()->default(null);
            $table->string('test_column_comma', 255)->nullable()->default(null);
            $table->string('test_column_double_quotation', 255)->nullable()->default(null);
            $table->string('test_column_line_break', 255)->nullable()->default(null);
            $table->string('test_column_mdc', 255)->nullable()->default(null);
            $table->string('test_column_half_width_kana', 255)->nullable()->default(null);
        });

        $mTablesLastRowId = \DB::table(static::$mTables)->count() == 0 ? 0 : \DB::table(static::$mTables)->orderBy('id', 'desc')->first()->id;
        $mTableId1 = $mTablesLastRowId + 1;
        $mTableColumnsLastRowId = \DB::table(static::$mTableColumns)->count() == 0 ? 0 : \DB::table(static::$mTableColumns)->orderBy('id', 'desc')->first()->id;

        \DB::table(static::$mTables)->insert([
            [
                'id' => $mTableId1,
                'table_name' => static::$rawData1,
                'table_name_alias' => 'テスト用_全カラムタイプ(E2Eテスト用)',
                'created_by' => null,
                'created_at' => '2020-07-29 14:20:29',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:20:29',
            ],
        ]);

        \DB::table(static::$mTableColumns)->insert([
            //xls_for_e2e_test
            [
                'id' => $mTableColumnsLastRowId + 1,
                'table_id' => $mTableId1,
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
                'id' => $mTableColumnsLastRowId + 2,
                'table_id' => $mTableId1,
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
                'id' => $mTableColumnsLastRowId + 3,
                'table_id' => $mTableId1,
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
                'id' => $mTableColumnsLastRowId + 4,
                'table_id' => $mTableId1,
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
                'id' => $mTableColumnsLastRowId + 5,
                'table_id' => $mTableId1,
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
            [
                'id' => $mTableColumnsLastRowId + 6,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_comma',
                'column_name_alias' => 'テスト列コンマ',
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
            [
                'id' => $mTableColumnsLastRowId + 7,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_double_quotation',
                'column_name_alias' => 'テスト列の二重引用符',
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
            [
                'id' => $mTableColumnsLastRowId + 8,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_line_break',
                'column_name_alias' => 'テスト列の改行',
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
            [
                'id' => $mTableColumnsLastRowId + 9,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_mdc',
                'column_name_alias' => 'テスト列のマシン依存文字',
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
            [
                'id' => $mTableColumnsLastRowId + 10,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_half_width_kana',
                'column_name_alias' => 'テスト列半角仮名',
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
        ]);

        $mDatasourcesLastRowId = \DB::table(static::$mDatasources)->count() == 0 ? 0 : \DB::table(static::$mDatasources)->orderBy('id', 'desc')->first()->id;
        $mDatasourcesId1 = $mDatasourcesLastRowId + 1;
        $mDatasourceColumnsLastRowId = \DB::table(static::$mDatasourceColumns)->count() == 0 ? 0 : \DB::table(static::$mDatasourceColumns)->orderBy('id', 'desc')->first()->id;

        \DB::table(static::$mDatasources)->insert([
            [
                'id' => $mDatasourcesLastRowId + 1,
                'datasource_name' => 'テストエクセル',
                'table_id' => $mTableId1,
                'starting_row_number' => 2,
                'created_by' => null,
                'created_at' => '2020-07-29 14:30:07',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:30:07',
            ],
        ]);

        \DB::table(static::$mDatasourceColumns)->insert([
            //テストエクセル
            [
                'id' => $mDatasourceColumnsLastRowId + 1,
                'table_column_id' => $mTableColumnsLastRowId + 1,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 1,
                'datasource_column_name' => '列A_BIGINT',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 2,
                'table_column_id' => $mTableColumnsLastRowId + 2,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 2,
                'datasource_column_name' => '列B_DATE',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 3,
                'table_column_id' => $mTableColumnsLastRowId + 3,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 3,
                'datasource_column_name' => '列C_DATETIME',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 4,
                'table_column_id' => $mTableColumnsLastRowId + 4,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 4,
                'datasource_column_name' => '列D_DECIMAL',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 5,
                'table_column_id' => $mTableColumnsLastRowId + 5,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 5,
                'datasource_column_name' => '列E_VARCHAR',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 6,
                'table_column_id' => $mTableColumnsLastRowId + 6,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 6,
                'datasource_column_name' => '列F_COMMA',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 7,
                'table_column_id' => $mTableColumnsLastRowId + 7,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 7,
                'datasource_column_name' => '列G_DOUBLE_QUOTATION',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 8,
                'table_column_id' => $mTableColumnsLastRowId + 8,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 8,
                'datasource_column_name' => '列H_LINE_BREAK',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 9,
                'table_column_id' => $mTableColumnsLastRowId + 9,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 9,
                'datasource_column_name' => '列I_MACHINE_DEPENDENT_CHAR',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColumnsLastRowId + 10,
                'table_column_id' => $mTableColumnsLastRowId + 10,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 10,
                'datasource_column_name' => '列J_HALF_WIDTH_KANA',
                'created_by' => null,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => null,
                'updated_at' => '2020-05-01 00:34:55',
            ],
        ]);
    }

    private function createNewTableWithoutColumnAndData()
    {
        Schema::create(static::$table_without_data_and_column, function (Blueprint $table) {
            $table->string('file_name');
            $table->bigInteger('file_id');
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();
        });

        $mTablesLastRowId = \DB::table(static::$mTables)->count() == 0 ? 0 : \DB::table(static::$mTables)->orderBy('id', 'desc')->first()->id;
        $mTableId1 = $mTablesLastRowId + 1;

        \DB::table(static::$mTables)->insert([
            [
                'id' => $mTableId1,
                'table_name' => static::$table_without_data_and_column,
                'table_name_alias' => '列とデータのないテストテーブル（E2Eテスト）',
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:20:29',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:20:29',
            ],
        ]);
    }

    private function createNewTableWithoutData()
    {
        Schema::create(static::$table_without_data, function (Blueprint $table) {
            $table->string('file_name');
            $table->bigInteger('file_id');
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->bigInteger('test_column_bigint')->charset(null)->nullable()->default(NULL);
            $table->date('test_column_date')->charset(null)->nullable()->default(NULL);
            $table->string('test_column_varchar', 255)->nullable()->default(NULL);
            $table->decimal('test_column_decimal', 65, 30)->charset(null)->nullable()->default(NULL);
            $table->dateTime('test_column_datetime', 0)->charset(null)->nullable()->default(NULL);
            $table->string('test_column_comma', 255)->nullable()->default(NULL);
            $table->string('test_column_double_quotation', 255)->nullable()->default(NULL);
            $table->string('test_column_line_break', 255)->nullable()->default(NULL);
            $table->string('test_column_mdc', 255)->nullable()->default(NULL);
            $table->string('test_column_half_width_kana', 255)->nullable()->default(NULL);
        });

        $mTablesLastRowId = \DB::table(static::$mTables)->count() == 0 ? 0 : \DB::table(static::$mTables)->orderBy('id', 'desc')->first()->id;
        $mTableId1 = $mTablesLastRowId + 1;
        $mTableColumnsLastRowId = \DB::table(static::$mTableColumns)->count() == 0 ? 0 : \DB::table(static::$mTableColumns)->orderBy('id', 'desc')->first()->id;

        \DB::table(static::$mTables)->insert([
            [
                'id' => $mTableId1,
                'table_name' => static::$table_without_data,
                'table_name_alias' => 'データのないテストテーブル（E2Eテスト）',
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:20:29',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:20:29',
            ],
        ]);

        \DB::table(static::$mTableColumns)->insert([
            //xls_for_e2e_test
            [
                'id' => $mTableColumnsLastRowId + 1,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_bigint',
                'column_name_alias' => 'カラム_BIGINT',
                'data_type' => 'bigint',
                'length' => 10,
                'maximum_number' => NULL,
                'decimal_part' => NULL,
                'validation' => NULL,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 2,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_date',
                'column_name_alias' => 'カラム_DATE',
                'data_type' => 'date',
                'length' => NULL,
                'maximum_number' => NULL,
                'decimal_part' => NULL,
                'validation' => NULL,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 3,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_datetime',
                'column_name_alias' => 'カラム_DATETIME',
                'data_type' => 'datetime',
                'length' => null,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 4,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_decimal',
                'column_name_alias' => 'カラム_DECIMAL',
                'data_type' => 'decimal',
                'length' => null,
                'maximum_number' => 8,
                'decimal_part' => 2,
                'validation' => null,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 5,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_varchar',
                'column_name_alias' => 'カラム_文字列',
                'data_type' => 'varchar',
                'length' => 255,
                'maximum_number' => NULL,
                'decimal_part' => NULL,
                'validation' => NULL,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 6,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_comma',
                'column_name_alias' => 'テスト列コンマ',
                'data_type' => 'varchar',
                'length' => 255,
                'maximum_number' => NULL,
                'decimal_part' => NULL,
                'validation' => NULL,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 7,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_double_quotation',
                'column_name_alias' => 'テスト列の二重引用符',
                'data_type' => 'varchar',
                'length' => 255,
                'maximum_number' => NULL,
                'decimal_part' => NULL,
                'validation' => NULL,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 8,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_line_break',
                'column_name_alias' => 'テスト列の改行',
                'data_type' => 'varchar',
                'length' => 255,
                'maximum_number' => NULL,
                'decimal_part' => NULL,
                'validation' => NULL,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 9,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_mdc',
                'column_name_alias' => 'テスト列のマシン依存文字',
                'data_type' => 'varchar',
                'length' => 255,
                'maximum_number' => NULL,
                'decimal_part' => NULL,
                'validation' => NULL,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
            [
                'id' => $mTableColumnsLastRowId + 10,
                'table_id' => $mTableId1,
                'column_name' => 'test_column_half_width_kana',
                'column_name_alias' => 'テスト列半角仮名',
                'data_type' => 'varchar',
                'length' => 255,
                'maximum_number' => NULL,
                'decimal_part' => NULL,
                'validation' => NULL,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:21:24',
            ],
        ]);

        $mDatasourcesLastRowId = \DB::table(static::$mDatasources)->count() == 0 ? 0 : \DB::table(static::$mDatasources)->orderBy('id', 'desc')->first()->id;
        $mDatasourcesId1 = $mDatasourcesLastRowId + 1;
        $mDatasourceColomnsLastRowId = \DB::table(static::$mDatasourceColumns)->count() == 0 ? 0 : \DB::table(static::$mDatasourceColumns)->orderBy('id', 'desc')->first()->id;

        \DB::table(static::$mDatasources)->insert([
            [
                'id' => $mDatasourcesLastRowId + 1,
                'datasource_name' => 'テストエクセル',
                'table_id' => $mTableId1,
                'starting_row_number' => 2,
                'created_by' => NULL,
                'created_at' => '2020-07-29 14:30:07',
                'updated_by' => NULL,
                'updated_at' => '2020-07-29 14:30:07',
            ],
        ]);

        \DB::table(static::$mDatasourceColumns)->insert([
            //テストエクセル
            [
                'id' => $mDatasourceColomnsLastRowId + 1,
                'table_column_id' => $mTableColumnsLastRowId + 1,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 1,
                'datasource_column_name' => '列A_BIGINT',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 2,
                'table_column_id' => $mTableColumnsLastRowId + 2,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 2,
                'datasource_column_name' => '列B_DATE',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 3,
                'table_column_id' => $mTableColumnsLastRowId + 3,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 3,
                'datasource_column_name' => '列C_DATETIME',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 4,
                'table_column_id' => $mTableColumnsLastRowId + 4,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 4,
                'datasource_column_name' => '列D_DECIMAL',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 5,
                'table_column_id' => $mTableColumnsLastRowId + 5,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 5,
                'datasource_column_name' => '列E_VARCHAR',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 6,
                'table_column_id' => $mTableColumnsLastRowId + 6,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 6,
                'datasource_column_name' => '列F_COMMA',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 7,
                'table_column_id' => $mTableColumnsLastRowId + 7,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 7,
                'datasource_column_name' => '列G_DOUBLE_QUOTATION',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 8,
                'table_column_id' => $mTableColumnsLastRowId + 8,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 8,
                'datasource_column_name' => '列H_LINE_BREAK',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 9,
                'table_column_id' => $mTableColumnsLastRowId + 9,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 9,
                'datasource_column_name' => '列I_MACHINE_DEPENDENT_CHAR',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
            [
                'id' => $mDatasourceColomnsLastRowId + 10,
                'table_column_id' => $mTableColumnsLastRowId + 10,
                'datasource_id' => $mDatasourcesId1,
                'datasource_column_number' => 10,
                'datasource_column_name' => '列J_HALF_WIDTH_KANA',
                'created_by' => NULL,
                'created_at' => '2020-05-01 00:34:55',
                'updated_by' => NULL,
                'updated_at' => '2020-05-01 00:34:55',
            ],
        ]);
    }
}
