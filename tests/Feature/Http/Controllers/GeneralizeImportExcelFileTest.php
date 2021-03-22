<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\Table;
use App\Models\Datasource;
use App\Models\TableColumns;
use App\Models\DatasourceColumns;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GeneralizeImportExcelFileTest extends TestCase
{
    // データベースの初期化にトランザクションを使う
    use RefreshDatabase;

    /**
     * 各テストメソッドの実行前に呼ばれるメソッド
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed");

        // テスト用テーブルのマイグレーション
        Schema::create(
            'xls_test',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('test_column_int')->nullable();
                $table->date('test_column_date')->nullable();
                $table->string('test_column_string', 255)->nullable();
                $table->decimal('test_column_decimal', 8, 2)->nullable();
            
                $table->tinyInteger('delete_flag')->nullable();
                $table->string('created_by', 255)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->string('updated_by', 255)->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedBigInteger('file_id')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->string('file_name', 255)->nullable();
            }
        );

        // テーブル定義などの設定
        // m_tables
        Table::query()->truncate();
        Table::updateOrCreate(
            ['id' => 100],
            [
                'table_name' => 'xls_test',
                'table_name_alias' => 'テスト',
            ]
        );
        // m_datasources
        Datasource::updateOrCreate(
            [
                'id' => 100
            ],
            [
                'datasource_name' => 'テスト'
            ]
        );
        // m_table_definitions
        TableColumns::query()->truncate();
        TableColumns::updateOrCreate(
            [
                'id' => '20001',
            ],
            [
                'table_id' => 100,
                'column_name' => 'test_column_int',
                'column_name_alias' => 'test_column_int',
                'data_type' => 'int',
            ]
        );
        TableColumns::updateOrCreate(
            [
                'id' => '20002',
            ],
            [
                'table_id' => 100,
                'column_name' => 'test_column_date',
                'column_name_alias' => 'test_column_date',
                'data_type' => 'date',
            ]
        );
        TableColumns::updateOrCreate(
            [
                'id' => '20003',
            ],
            [
                'table_id' => 100,
                'column_name' => 'test_column_string',
                'column_name_alias' => 'test_column_string',
                'data_type' => 'varchar',
                'length' => 255,
            ]
        );
        TableColumns::updateOrCreate(
            [
                'id' => '20004',
            ],
            [
                'table_id' => 100,
                'column_name' => 'test_column_decimal',
                'column_name_alias' => 'test_column_decimal',
                'data_type' => 'decimal',
                'maximum_number' => 8,
                'decimal_part' => 2
            ]
        );
        // m_data_column_mappings
        DatasourceColumns::updateOrCreate(
            [
                'table_column_id'   => '20001'
            ],
            [
                'datasource_id'         => '100',
                'datasource_column_number'          => 1
            ]
        );
        DatasourceColumns::updateOrCreate(
            [
                'table_column_id'   => '20002'
            ],
            [
                'datasource_id'         => '100',
                'datasource_column_number'          => 2
            ]
        );
        DatasourceColumns::updateOrCreate(
            [
                'table_column_id'   => '20003'
            ],
            [
                'datasource_id'         => '100',
                'datasource_column_number'          => 3
            ]
        );
        DatasourceColumns::updateOrCreate(
            [
                'table_column_id'   => '20004'
            ],
            [
                'datasource_id'         => '100',
                'datasource_column_number'          => 4
            ]
        );
    }

    /**
     * 各テストメソッドの実行後に呼ばれるメソッド
     */
    protected function tearDown(): void
    {
        // m_tables
        Table::where('id', 100)->forceDelete();
        // m_datasources
        Datasource::where('id', 100)->forceDelete();
        // m_table_definitions
        TableColumns::where('id', '20001')->forceDelete();
        TableColumns::where('id', '20002')->forceDelete();
        TableColumns::where('id', '20003')->forceDelete();
        TableColumns::where('id', '20004')->forceDelete();
        // m_data_column_mappings
        DatasourceColumns::where(['datasource_id' => '100', 'datasource_column_number' => 1])->forceDelete();
        DatasourceColumns::where(['datasource_id' => '100', 'datasource_column_number' => 2])->forceDelete();
        DatasourceColumns::where(['datasource_id' => '100', 'datasource_column_number' => 3])->forceDelete();
        DatasourceColumns::where(['datasource_id' => '100', 'datasource_column_number' => 4])->forceDelete();
        // xls_test
        Schema::dropIfExists('xls_test');
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testマッピングの通りにDB登録できる()
    {
        Storage::fake('public');
        $response = $this->post(
            '/upload-excel',
            [
            'file' => new UploadedFile(base_path('tests/misc/excel/test_generalize_import.xlsx'), 'test_generalize_import.xlsx', null, null, true),
            'datasource_id' => 100,
            'sheet_name' => 'Sheet1',
            'start_row' => 2,
            'end_row' => 2,
            'mode' => 'append'
            ]
        );

        $response->assertStatus(200);
        
        $this->assertDatabaseHas(
            'files',
            [
            'datasource_id' => '100',
            'table_name' => 'xls_test',
            'original_name' => 'test_generalize_import.xlsx'
            ]
        );
        // 設定されたマッピング通りにデータが取り込める
        $this->assertDatabaseHas(
            'xls_test',
            [
            'test_column_int' => 1,
            'test_column_date' => '2020-01-01',
            'test_column_string' => 'a',
            'test_column_decimal' => '1.11',
            'file_name' => 'test_generalize_import.xlsx'
            ]
        );
    }

    /**
     * @return void
     */
    public function testマッピングを変更してもDB登録できる()
    {
        // マッピングを変更
        DatasourceColumns::where(['table_column_id' => '20001'])
            ->update(['datasource_column_number' => 5]);
        DatasourceColumns::where(['table_column_id' => '20002'])
            ->update(['datasource_column_number' => 6]);
        DatasourceColumns::where(['table_column_id' => '20003'])
            ->update(['datasource_column_number' => 7]);
        DatasourceColumns::where(['table_column_id' => '20004'])
            ->update(['datasource_column_number' => 8]);


        Storage::fake('public');
        $response = $this->post(
            '/upload-excel',
            [
            'file' => new UploadedFile(base_path('tests/misc/excel/test_generalize_import.xlsx'), 'test_generalize_import.xlsx', null, null, true),
            'datasource_id' => 100,
            'sheet_name' => 'Sheet1',
            'start_row' => 2,
            'end_row' => 2,
            'mode' => 'append'
            ]
        );

        $response->assertStatus(200);
        
        $this->assertDatabaseHas(
            'files',
            [
            'datasource_id' => '100',
            'table_name' => 'xls_test',
            'original_name' => 'test_generalize_import.xlsx'
            ]
        );
        // 更新したマッピングの通りにでデータが取り込める
        $this->assertDatabaseHas(
            'xls_test',
            [
            'test_column_int' => 2,
            'test_column_date' => '2020-02-02',
            'test_column_string' => 'b',
            'test_column_decimal' => '2.22',
            'file_name' => 'test_generalize_import.xlsx'
            ]
        );
        
        // 変更したマッピングを戻す
        DatasourceColumns::where(['table_column_id' => '20001'])
            ->update(['datasource_column_number' => 1]);
        DatasourceColumns::where(['table_column_id' => '20002'])
            ->update(['datasource_column_number' => 2]);
        DatasourceColumns::where(['table_column_id' => '20003'])
            ->update(['datasource_column_number' => 3]);
        DatasourceColumns::where(['table_column_id' => '20004'])
            ->update(['datasource_column_number' => 4]);
    }

    /**
     * @return void
     */
    public function testデータソースを追加してもDB登録できる()
    {
        // データソースを追加
        // m_datasources
        Datasource::updateOrCreate(
            [
                'id' => 200
            ],
            [
                'datasource_name' => 'テスト2'
            ]
        );
        // m_data_column_mappings
        DatasourceColumns::updateOrCreate(
            [
                'table_column_id'   => '20001'
            ],
            [
                'datasource_id'         => '200',
                'datasource_column_number'          => 1
            ]
        );
        DatasourceColumns::updateOrCreate(
            [
                'table_column_id'   => '20002'
            ],
            [
                'datasource_id'         => '200',
                'datasource_column_number'          => 3
            ]
        );
        DatasourceColumns::updateOrCreate(
            [
                'table_column_id'   => '20003'
            ],
            [
                'datasource_id'         => '200',
                'datasource_column_number'          => 5
            ]
        );
        DatasourceColumns::updateOrCreate(
            [
                'table_column_id'   => '20004'
            ],
            [
                'datasource_id'         => '200',
                'datasource_column_number'          => 7
            ]
        );

        Storage::fake('public');
        $response = $this->post(
            '/upload-excel',
            [
            'file' => new UploadedFile(base_path('tests/misc/excel/test_generalize_import.xlsx'), 'test_generalize_import.xlsx', null, null, true),
            'datasource_id' => 200,
            'sheet_name' => 'Sheet2',
            'start_row' => 2,
            'end_row' => 2,
            'mode' => 'append'
            ]
        );

        $response->assertStatus(200);
        
        $this->assertDatabaseHas(
            'files',
            [
            'datasource_id' => '100',
            'table_name' => 'xls_test',
            'original_name' => 'test_generalize_import.xlsx'
            ]
        );
        // 更新したマッピングの通りにでデータが取り込める
        $this->assertDatabaseHas(
            'xls_test',
            [
            'test_column_int' => 1,
            'test_column_date' => '2020-01-01',
            'test_column_string' => 'a',
            'test_column_decimal' => '1.11',
            'file_name' => 'test_generalize_import.xlsx'
            ]
        );

        // 追加したデータソースを削除
        // m_datasources
        Datasource::where('id', '200')->forceDelete();
        // m_data_column_mappings
        DatasourceColumns::where(['datasource_id' => '200', 'datasource_column_number' => 1])->forceDelete();
        DatasourceColumns::where(['datasource_id' => '200', 'datasource_column_number' => 3])->forceDelete();
        DatasourceColumns::where(['datasource_id' => '200', 'datasource_column_number' => 5])->forceDelete();
        DatasourceColumns::where(['datasource_id' => '200', 'datasource_column_number' => 7])->forceDelete();
    }

    /**
     * @return void
     */
    public function testカラムを追加してもDB登録できる()
    {
        // カラムを追加
        Schema::table(
            'xls_test',
            function (Blueprint $table) {
                $table->integer('test_column_int_2')->nullable();
            }
        );
        TableColumns::updateOrCreate(
            [
                'id' => '20005',
            ],
            [
                'table_id' => 100,
                'column_name' => 'test_column_int_2',
                'column_name_alias' => 'test_column_int_2',
                'data_type' => 'int',
            ]
        );
        DatasourceColumns::updateOrCreate(
            [
                'datasource_id'         => '100',
                'datasource_column_number'          => 5
            ],
            [
                'table_column_id'   => '20005'
            ]
        );


        Storage::fake('public');
        $response = $this->post(
            '/upload-excel',
            [
            'file' => new UploadedFile(base_path('tests/misc/excel/test_generalize_import.xlsx'), 'test_generalize_import.xlsx', null, null, true),
            'datasource_id' => 100,
            'sheet_name' => 'Sheet1',
            'start_row' => 2,
            'end_row' => 2,
            'mode' => 'append'
            ]
        );

        $response->assertStatus(200);
        
        $this->assertDatabaseHas(
            'files',
            [
            'datasource_id' => '100',
            'table_name' => 'xls_test',
            'original_name' => 'test_generalize_import.xlsx'
            ]
        );
        // 更新したマッピングの通りにでデータが取り込める
        $this->assertDatabaseHas(
            'xls_test',
            [
            'test_column_int' => 1,
            'test_column_date' => '2020-01-01',
            'test_column_string' => 'a',
            'test_column_decimal' => '1.11',
            'test_column_int_2' => 2,
            'file_name' => 'test_generalize_import.xlsx'
            ]
        );

        // 追加したカラムを削除する
        TableColumns::where(['id' => '20005',])->forceDelete();
        DatasourceColumns::where(['datasource_id' => '100', 'datasource_column_number' => 5])->forceDelete();
    }

    /**
     * @return void
     */
    public function testカラムを削除してもDB登録できる()
    {
        // カラムを削除
        Schema::table(
            'xls_test',
            function (Blueprint $table) {
                $table->dropColumn('test_column_int');
            }
        );
        TableColumns::where('id', '20001')->delete();
        DatasourceColumns::where(['datasource_id' => '100', 'datasource_column_number' => 1])->delete();

        Storage::fake('public');
        $response = $this->post(
            '/upload-excel',
            [
            'file' => new UploadedFile(base_path('tests/misc/excel/test_generalize_import.xlsx'), 'test_generalize_import.xlsx', null, null, true),
            'datasource_id' => 100,
            'sheet_name' => 'Sheet1',
            'start_row' => 2,
            'end_row' => 2,
            'mode' => 'append'
            ]
        );

        $response->assertStatus(200);
        
        $this->assertDatabaseHas(
            'files',
            [
            'datasource_id' => '100',
            'table_name' => 'xls_test',
            'original_name' => 'test_generalize_import.xlsx'
            ]
        );
        // 更新したマッピングの通りにでデータが取り込める
        $this->assertDatabaseHas(
            'xls_test',
            [
            'test_column_date' => '2020-01-01',
            'test_column_string' => 'a',
            'test_column_decimal' => '1.11',
            'file_name' => 'test_generalize_import.xlsx'
            ]
        );

        // 削除したカラムを追加
        TableColumns::withTrashed()->where('id', '20001')->restore();
        DatasourceColumns::updateOrCreate(
            [
                'datasource_id'         => '100',
                'datasource_column_number'          => 1
            ],
            [
                'table_column_id'   => '20001'
            ]
        );
    }

    /**
     * @return void
     */
    public function testテーブル定義（日付→varchar）を変更してもDB登録できる()
    {
        // テーブル定義を変更
        Schema::table(
            'xls_test',
            function (Blueprint $table) {
                $table->string('test_column_date', 255)->nullable()->change();
            }
        );
        TableColumns::updateOrCreate(
            [
                'id' => '20002',
            ],
            [
                'data_type' => 'varchar',
                'length' => 255,
            ]
        );

        Storage::fake('public');
        $response = $this->post(
            '/upload-excel',
            [
            'file' => new UploadedFile(base_path('tests/misc/excel/test_generalize_import.xlsx'), 'test_generalize_import.xlsx', null, null, true),
            'datasource_id' => 100,
            'sheet_name' => 'Sheet1',
            'start_row' => 2,
            'end_row' => 2,
            'mode' => 'append'
            ]
        );

        $response->assertStatus(200);
        
        $this->assertDatabaseHas(
            'files',
            [
            'datasource_id' => '100',
            'table_name' => 'xls_test',
            'original_name' => 'test_generalize_import.xlsx'
            ]
        );
        // 更新したマッピングの通りにでデータが取り込める
        $this->assertDatabaseHas(
            'xls_test',
            [
            'test_column_int' => 1,
            'test_column_date' => '43831',
            'test_column_string' => 'a',
            'test_column_decimal' => '1.11',
            'file_name' => 'test_generalize_import.xlsx'
            ]
        );
        
        // テーブル定義を戻す
        TableColumns::updateOrCreate(
            [
                'id' => '20002',
            ],
            [
                'data_type' => 'date',
                'length' => null,
            ]
        );
    }

    /**
     * @return void
     */
    public function testテーブル定義（小数の桁数変更）を変更してもDB登録できる()
    {
        // テーブル定義を変更
        Schema::table(
            'xls_test',
            function (Blueprint $table) {
                $table->decimal('test_column_decimal', 8, 1)->change();
            }
        );
        TableColumns::updateOrCreate(
            [
                'id' => '20004',
            ],
            [
                'maximum_number' => 8,
                'decimal_part' => 1
            ]
        );

        Storage::fake('public');
        $response = $this->post(
            '/upload-excel',
            [
            'file' => new UploadedFile(base_path('tests/misc/excel/test_generalize_import.xlsx'), 'test_generalize_import.xlsx', null, null, true),
            'datasource_id' => 100,
            'sheet_name' => 'Sheet1',
            'start_row' => 2,
            'end_row' => 2,
            'mode' => 'append'
            ]
        );

        $response->assertStatus(200);
        
        $this->assertDatabaseHas(
            'files',
            [
            'datasource_id' => '100',
            'table_name' => 'xls_test',
            'original_name' => 'test_generalize_import.xlsx'
            ]
        );
        // 更新したマッピングの通りにでデータが取り込める
        $this->assertDatabaseHas(
            'xls_test',
            [
            'test_column_int' => 1,
            'test_column_date' => '2020-01-01',
            'test_column_string' => 'a',
            'test_column_decimal' => '1.1',
            'file_name' => 'test_generalize_import.xlsx'
            ]
        );

        // テーブル定義を戻す
        TableColumns::updateOrCreate(
            [
                'id' => '20004',
            ],
            [
                'maximum_number' => 8,
                'decimal_part' => 2
            ]
        );
    }

    /**
     * @return void
     */
    public function testカラム名を変更してもDB登録できる()
    {
        // カラム名を変更
        Schema::table(
            'xls_test',
            function (Blueprint $table) {
                $table->renameColumn('test_column_date', 'new_test_column_date');
            }
        );
        TableColumns::updateOrCreate(
            [
                'id' => '20002',
            ],
            [
                'column_name' => 'new_test_column_date',
                'column_name_alias' => 'new_test_column_date',
            ]
        );

        Storage::fake('public');
        $response = $this->post(
            '/upload-excel',
            [
            'file' => new UploadedFile(base_path('tests/misc/excel/test_generalize_import.xlsx'), 'test_generalize_import.xlsx', null, null, true),
            'datasource_id' => 100,
            'sheet_name' => 'Sheet1',
            'start_row' => 2,
            'end_row' => 2,
            'mode' => 'append'
            ]
        );

        $response->assertStatus(200);
        
        $this->assertDatabaseHas(
            'files',
            [
            'datasource_id' => '100',
            'table_name' => 'xls_test',
            'original_name' => 'test_generalize_import.xlsx'
            ]
        );
        // 更新したマッピングの通りにでデータが取り込める
        $this->assertDatabaseHas(
            'xls_test',
            [
            'test_column_int' => 1,
            'new_test_column_date' => '2020-01-01',
            'test_column_string' => 'a',
            'test_column_decimal' => '1.11',
            'file_name' => 'test_generalize_import.xlsx'
            ]
        );

        // 変更したカラム名を戻す
        TableColumns::updateOrCreate(
            [
                'id' => '20002',
            ],
            [
                'column_name' => 'test_column_date',
                'column_name_alias' => 'test_column_date',
            ]
        );
    }
}
