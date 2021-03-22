<?php

namespace Tests\Feature;

use App\Models\Table;
use App\Models\TableColumns;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use DB;
use Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class APIUploadDefinitionBulkControllerForNewTest extends TestCase
{
    // Refresh all data in database using transaction
    // use RefreshDatabase;
    // use DatabaseTransactions;

    /**
     * Setup
     */
    public function setUp(): void
    {
        parent::setUp();

        // delete all using table records
        Table::query()->truncate();
        TableColumns::query()->truncate();
        DataSource::query()->truncate();
        DatasourceColumns::query()->truncate();


        //Clean up
        Schema::dropIfExists('table_test_new');
    }

    private function countNumberOfTableOnDatabase()
    {
        return count(DB::select('SHOW TABLES'));
    }

    /**
     * Able to upload and insert new definitions(tables, table_columns, datasources, datasource_columns)
     * 定義一括Excelがアップロードでき、定義情報がすべて新規追加できる
     */
    public function test_AbleToUploadBulkDefinedExcel()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = '新規追加テーブル';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        //予想結果 excepted result
        $exceptedTable = [
            'table_name' => 'table_test_new',
            'table_name_alias' => 'テーブルテスト新規追加',
        ];
        $exceptedTableColumns = [
            [
                'column_name' => 'table_column1_BIGINT',
                'column_name_alias' => 'カラム１_BIGINT',
                'length' => 20,
                'maximum_number' => null,
                'decimal_part' => null,
                'data_type' => 'bigint',    //typeは小文字変換される
                'validation' => 'validation1',
            ],
            [
                'column_name' => 'table_column2_DATE',
                'column_name_alias' => 'カラム２_DATE',
                'length' => null,
                'maximum_number' => null,
                'decimal_part' => null,
                'data_type' => 'date',  //typeは小文字変換される
                'validation' => null,   //date型の場合validationに何が入っていてもnullとなる
            ],
            [
                'column_name' => 'table_column3_DATETIME',
                'column_name_alias' => 'カラム３_DATETIME',
                'length' => null,
                'maximum_number' => null,
                'decimal_part' => null,
                'data_type' => 'datetime',  //typeは小文字変換される
                'validation' => null,   //datetime型の場合validationに何が入っていてもnullとなる
            ],
            [
                'column_name' => 'table_column4_DECIMAL',
                'column_name_alias' => 'カラム４_DECIMAL',
                'length' => null,
                'maximum_number' => 20,
                'decimal_part' => 3,
                'data_type' => 'decimal',   //typeは小文字変換される
                'validation' => 'validation4',
            ],
            [
                'column_name' => 'table_column5_VARCHAR',
                'column_name_alias' => 'カラム５_VARCHAR',
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'data_type' => 'varchar',   //typeは小文字変換される
                'validation' => 'validation5',
            ],
        ];
        $exceptedDatasource = [
            'datasource_name'       => 'datasource_new',
            'starting_row_number'   => 2,
        ];
        $exceptedDatasourceColumns = [
            [
                'datasource_column_number' => 1,
                'datasource_column_name' => 'Excel列A_BIGINT'
            ],
            [
                'datasource_column_number' => 2,
                'datasource_column_name' => 'Excel列B_DATE'
            ],
            [
                'datasource_column_number' => 3,
                'datasource_column_name' => 'Excel列C_DATETIME'
            ],
            [
                'datasource_column_number' => 4,
                'datasource_column_name' => 'Excel列D_DECIMAL'
            ],
            [
                'datasource_column_number' => 5,
                'datasource_column_name' => 'Excel列E_VARCHAR'
            ],
        ];


        // RESPONSE ********************
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['table_name' => $exceptedTable['table_name']])
            ->assertJsonFragment(['datasource_name' => $exceptedDatasource['datasource_name']]);


        // TABLE ********************
        //結果取得 get result
        $lastInsertedTable = Table::orderBy('id', 'DESC')->first();

        // Check table data of 'm_table'
        $this->assertEquals($exceptedTable['table_name'], $lastInsertedTable->table_name);
        $this->assertEquals($exceptedTable['table_name_alias'], $lastInsertedTable->table_name_alias);
        $this->assertTrue($lastInsertedTable->created_at != null);
        $this->assertTrue($lastInsertedTable->updated_at != null);
        $this->assertTrue($lastInsertedTable->created_by == null);
        $this->assertTrue($lastInsertedTable->updated_by == null);

        // Check table existence on Database
        $this->assertTrue(Schema::hasTable($exceptedTable['table_name']));
        // Check column existence of following table
        $this->assertTrue(Schema::hasColumns($exceptedTable['table_name'], [
            'file_name',
            'file_id',
            'created_by',
            'created_at',
        ]));

        // check nullable
        $con = DB::connection();
        $this->assertEquals(true, $con->getDoctrineColumn($exceptedTable['table_name'], 'file_name')->getNotNull());
        $this->assertEquals(true, $con->getDoctrineColumn($exceptedTable['table_name'], 'file_id')->getNotNull());
        $this->assertEquals(false, $con->getDoctrineColumn($exceptedTable['table_name'], 'created_by')->getNotNull());
        $this->assertEquals(false, $con->getDoctrineColumn($exceptedTable['table_name'], 'created_at')->getNotNull());

        // check default
        $this->assertEquals(null, $con->getDoctrineColumn($exceptedTable['table_name'], 'file_name')->getDefault());
        $this->assertEquals(null, $con->getDoctrineColumn($exceptedTable['table_name'], 'file_id')->getDefault());
        $this->assertEquals(null, $con->getDoctrineColumn($exceptedTable['table_name'], 'created_by')->getDefault());
        $this->assertEquals(null, $con->getDoctrineColumn($exceptedTable['table_name'], 'created_at')->getDefault());


        // TABLE_COLUMNS ********************
        //結果取得 get result
        $insertedTableColumns = TableColumns::where('table_id', $lastInsertedTable->id)->orderBy('id', 'asc')->get();

        // Check table data of 'm_table_columns'
        $i = 0; //BIGINT
        $this->assertEquals($lastInsertedTable->id, $insertedTableColumns[$i]->table_id);
        $this->assertEquals($exceptedTableColumns[$i]['column_name'], $insertedTableColumns[$i]->column_name);
        $this->assertEquals($exceptedTableColumns[$i]['column_name_alias'], $insertedTableColumns[$i]->column_name_alias);
        $this->assertEquals($exceptedTableColumns[$i]['length'], $insertedTableColumns[$i]->length);
        $this->assertEquals($exceptedTableColumns[$i]['maximum_number'], $insertedTableColumns[$i]->maximum_number);
        $this->assertEquals($exceptedTableColumns[$i]['decimal_part'], $insertedTableColumns[$i]->decimal_part);
        $this->assertEquals($exceptedTableColumns[$i]['data_type'], $insertedTableColumns[$i]->data_type);
        $this->assertEquals($exceptedTableColumns[$i]['validation'], $insertedTableColumns[$i]->validation);
        $this->assertTrue($insertedTableColumns[$i]->created_at != null);
        $this->assertTrue($insertedTableColumns[$i]->updated_at != null);
        $this->assertTrue($insertedTableColumns[$i]->created_by == null);
        $this->assertTrue($insertedTableColumns[$i]->updated_by == null);
        $i = 1; //DATE
        $this->assertEquals($lastInsertedTable->id, $insertedTableColumns[$i]->table_id);
        $this->assertEquals($exceptedTableColumns[$i]['column_name'], $insertedTableColumns[$i]->column_name);
        $this->assertEquals($exceptedTableColumns[$i]['column_name_alias'], $insertedTableColumns[$i]->column_name_alias);
        $this->assertEquals($exceptedTableColumns[$i]['length'], $insertedTableColumns[$i]->length);
        $this->assertEquals($exceptedTableColumns[$i]['maximum_number'], $insertedTableColumns[$i]->maximum_number);
        $this->assertEquals($exceptedTableColumns[$i]['decimal_part'], $insertedTableColumns[$i]->decimal_part);
        $this->assertEquals($exceptedTableColumns[$i]['data_type'], $insertedTableColumns[$i]->data_type);
        $this->assertEquals($exceptedTableColumns[$i]['validation'], $insertedTableColumns[$i]->validation);
        $this->assertTrue($insertedTableColumns[$i]->created_at != null);
        $this->assertTrue($insertedTableColumns[$i]->updated_at != null);
        $this->assertTrue($insertedTableColumns[$i]->created_by == null);
        $this->assertTrue($insertedTableColumns[$i]->updated_by == null);
        $i = 2; //DATETIME
        $this->assertEquals($lastInsertedTable->id, $insertedTableColumns[$i]->table_id);
        $this->assertEquals($exceptedTableColumns[$i]['column_name'], $insertedTableColumns[$i]->column_name);
        $this->assertEquals($exceptedTableColumns[$i]['column_name_alias'], $insertedTableColumns[$i]->column_name_alias);
        $this->assertEquals($exceptedTableColumns[$i]['length'], $insertedTableColumns[$i]->length);
        $this->assertEquals($exceptedTableColumns[$i]['maximum_number'], $insertedTableColumns[$i]->maximum_number);
        $this->assertEquals($exceptedTableColumns[$i]['decimal_part'], $insertedTableColumns[$i]->decimal_part);
        $this->assertEquals($exceptedTableColumns[$i]['data_type'], $insertedTableColumns[$i]->data_type);
        $this->assertEquals($exceptedTableColumns[$i]['validation'], $insertedTableColumns[$i]->validation);
        $this->assertTrue($insertedTableColumns[$i]->created_at != null);
        $this->assertTrue($insertedTableColumns[$i]->updated_at != null);
        $this->assertTrue($insertedTableColumns[$i]->created_by == null);
        $this->assertTrue($insertedTableColumns[$i]->updated_by == null);
        $i = 3; //DECIMAL
        $this->assertEquals($lastInsertedTable->id, $insertedTableColumns[$i]->table_id);
        $this->assertEquals($exceptedTableColumns[$i]['column_name'], $insertedTableColumns[$i]->column_name);
        $this->assertEquals($exceptedTableColumns[$i]['column_name_alias'], $insertedTableColumns[$i]->column_name_alias);
        $this->assertEquals($exceptedTableColumns[$i]['length'], $insertedTableColumns[$i]->length);
        $this->assertEquals($exceptedTableColumns[$i]['maximum_number'], $insertedTableColumns[$i]->maximum_number);
        $this->assertEquals($exceptedTableColumns[$i]['decimal_part'], $insertedTableColumns[$i]->decimal_part);
        $this->assertEquals($exceptedTableColumns[$i]['data_type'], $insertedTableColumns[$i]->data_type);
        $this->assertEquals($exceptedTableColumns[$i]['validation'], $insertedTableColumns[$i]->validation);
        $this->assertTrue($insertedTableColumns[$i]->created_at != null);
        $this->assertTrue($insertedTableColumns[$i]->updated_at != null);
        $this->assertTrue($insertedTableColumns[$i]->created_by == null);
        $this->assertTrue($insertedTableColumns[$i]->updated_by == null);
        $i = 4; //VARCHAR
        $this->assertEquals($lastInsertedTable->id, $insertedTableColumns[$i]->table_id);
        $this->assertEquals($exceptedTableColumns[$i]['column_name'], $insertedTableColumns[$i]->column_name);
        $this->assertEquals($exceptedTableColumns[$i]['column_name_alias'], $insertedTableColumns[$i]->column_name_alias);
        $this->assertEquals($exceptedTableColumns[$i]['length'], $insertedTableColumns[$i]->length);
        $this->assertEquals($exceptedTableColumns[$i]['maximum_number'], $insertedTableColumns[$i]->maximum_number);
        $this->assertEquals($exceptedTableColumns[$i]['decimal_part'], $insertedTableColumns[$i]->decimal_part);
        $this->assertEquals($exceptedTableColumns[$i]['data_type'], $insertedTableColumns[$i]->data_type);
        $this->assertEquals($exceptedTableColumns[$i]['validation'], $insertedTableColumns[$i]->validation);
        $this->assertTrue($insertedTableColumns[$i]->created_at != null);
        $this->assertTrue($insertedTableColumns[$i]->updated_at != null);
        $this->assertTrue($insertedTableColumns[$i]->created_by == null);
        $this->assertTrue($insertedTableColumns[$i]->updated_by == null);

        // Check existence on DB
        $this->assertTrue(Schema::hasColumn($exceptedTable['table_name'], $exceptedTableColumns[0]['column_name']));
        $this->assertTrue(Schema::hasColumn($exceptedTable['table_name'], $exceptedTableColumns[1]['column_name']));
        $this->assertTrue(Schema::hasColumn($exceptedTable['table_name'], $exceptedTableColumns[2]['column_name']));
        $this->assertTrue(Schema::hasColumn($exceptedTable['table_name'], $exceptedTableColumns[3]['column_name']));
        $this->assertTrue(Schema::hasColumn($exceptedTable['table_name'], $exceptedTableColumns[4]['column_name']));

        // Check total column number
        $columnsCount = sizeof(Schema::getColumnListing($exceptedTable['table_name']));
        $this->assertEquals(9, $columnsCount); // Table Create API add 4 columns. So 4+5 = 9

        //Check column type from Database
        // 0: BIGINT
        $this->assertEquals($exceptedTableColumns[0]['data_type'], Schema::getColumnType($exceptedTable['table_name'], $exceptedTableColumns[0]['column_name']));
        //Bigint doesn't support any length.
        // 1: DATE
        $this->assertEquals($exceptedTableColumns[1]['data_type'], Schema::getColumnType($exceptedTable['table_name'], $exceptedTableColumns[1]['column_name']));
        // 2: DATETIME
        $this->assertEquals($exceptedTableColumns[1]['data_type'], Schema::getColumnType($exceptedTable['table_name'], $exceptedTableColumns[1]['column_name']));
        // 3:DECIMAL
        $this->assertEquals($exceptedTableColumns[2]['data_type'], Schema::getColumnType($exceptedTable['table_name'], $exceptedTableColumns[2]['column_name']));
        //check column max_number and decimal part
        $con = DB::connection();
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[3]['column_name']);
        $this->assertEquals($exceptedTableColumns[3]['maximum_number'], $column->getPrecision());
        $this->assertEquals($exceptedTableColumns[3]['decimal_part'], $column->getScale());
        // 4: VARCHAR
        $this->assertEquals('string', Schema::getColumnType($exceptedTable['table_name'], $exceptedTableColumns[4]['column_name']));
        //check column length
        $con = DB::connection();
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[4]['column_name']);
        $this->assertEquals($exceptedTableColumns[4]['length'], $column->getLength());


        // check nullable
        $con = DB::connection();
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[0]['column_name']);
        $this->assertEquals(false, $column->getNotnull());
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[1]['column_name']);
        $this->assertEquals(false, $column->getNotnull());
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[2]['column_name']);
        $this->assertEquals(false, $column->getNotnull());
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[3]['column_name']);
        $this->assertEquals(false, $column->getNotnull());
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[4]['column_name']);
        $this->assertEquals(false, $column->getNotnull());

        // check default
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[0]['column_name']);
        $this->assertEquals(null, $column->getDefault());
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[1]['column_name']);
        $this->assertEquals(null, $column->getDefault());
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[2]['column_name']);
        $this->assertEquals(null, $column->getDefault());
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[3]['column_name']);
        $this->assertEquals(null, $column->getDefault());
        $column = $con->getDoctrineColumn($exceptedTable['table_name'], $exceptedTableColumns[4]['column_name']);
        $this->assertEquals(null, $column->getDefault());


        // DATASOURCE ********************
        //結果取得 get result
        $lastInsertedDatasource = Datasource::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasources' table
        $this->assertEquals($lastInsertedTable->id, $lastInsertedDatasource->table_id);
        $this->assertEquals($exceptedDatasource['datasource_name'], $lastInsertedDatasource->datasource_name);
        $this->assertEquals($exceptedDatasource['starting_row_number'], $lastInsertedDatasource->starting_row_number);
        $this->assertTrue($lastInsertedDatasource->created_at != null);
        $this->assertTrue($lastInsertedDatasource->updated_at != null);
        $this->assertTrue($lastInsertedDatasource->created_by == null);
        $this->assertTrue($lastInsertedDatasource->updated_by == null);



        // DATASOURCE_COLUMNS ********************
        //結果取得 get result
        $insertedDatasourceColumns = DatasourceColumns::where('datasource_id', $lastInsertedDatasource->id)->orderBy('id', 'asc')->get();

        // Check table data of 'm_datasource_columns'
        $i = 0;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 1;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 2;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 3;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 4;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);


        //Clean up
        Schema::dropIfExists($exceptedTable['table_name']); // Deleting table technically deletes column.
    }


    /**
     * API Validation - Required
     * API 必須項目の入力チェック
     */
    public function test_parametersAreAllRequired()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        // nothing
        // Set parameters for posting
        $sheetName = null;

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => null,
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 10])
            ->assertJsonFragment(['error_details' => [
                "アップロードファイルは必ず指定してください。",
                "シート名は必ず指定してください。"
            ]]);
    }

    /**
     * Confirm response when uploading not excel file.
     * Excel以外のファイルがアップロードされた場合に、期待するエラーが返却されるかを確認する。
     */
    public function test_FileExtensionIsNotExcel()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/txt/test_not_excel.txt');
        $originalName = 'test_not_excel.txt';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = '新規追加テーブル';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 10])
            ->assertJsonFragment(['error_details' => ["アップロードファイルにはxls, xlsxタイプのファイルを指定してください。"]]);
    }

    /**
     * Input Pattern 1 - If excel header is not Empty and column name is empty, ignore target row
     * Excelで想定される入力パターン 組み合わせのチェック１
     * -> Excelヘッダ名あり・カラム名なし　→ エラーなし＆table_column/datasource_columnにレコードはできない
     */
    public function test_InputPattern_IfExcelHeaderIsNotEmptyAndColumnNameIsEmpty_IgnoreTargetRow()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = '組み合わせチェック1_ヘッダ名有_カラム名無';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        //予想結果 excepted result
        $exceptedTable = [
            'table_name' => 'table_test_new',
            'table_name_alias' => 'テーブルテスト新規追加',
        ];
        $exceptedTableColumns = [
            [
                'column_name' => 'table_column2_VARCHAR',
                'column_name_alias' => 'カラム２_VARCHAR',
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'data_type' => 'varchar',  //typeは小文字変換される
                'validation' => 'validation2',
            ],
        ];
        $exceptedDatasource = [
            'datasource_name'       => 'datasource_new',
            'starting_row_number'   => 2,
        ];
        $exceptedDatasourceColumns = [
            [
                'datasource_column_number' => 2,
                'datasource_column_name' => 'Excel列B_VARCHAR'
            ],
        ];


        // RESPONSE ********************
        $response
            ->assertStatus(200);

        // TABLE ********************
        //結果取得 get result
        $lastInsertedTable = Table::orderBy('id', 'DESC')->first();

        // Check table data of 'm_table'
        $this->assertEquals($exceptedTable['table_name'], $lastInsertedTable->table_name);
        $this->assertEquals($exceptedTable['table_name_alias'], $lastInsertedTable->table_name_alias);

        // Check table existence on Database
        $this->assertTrue(Schema::hasTable($exceptedTable['table_name']));


        // TABLE_COLUMNS ********************
        //結果取得 get result
        $insertedTableColumns = TableColumns::where('table_id', $lastInsertedTable->id)->orderBy('id', 'asc')->get();

        // Check number of inserted record(s)
        $this->assertEquals(count($exceptedTableColumns), $insertedTableColumns->count());

        // Check table data of 'm_table_columns'
        $i = 0; //VARCHARのみであることを確認
        $this->assertEquals($lastInsertedTable->id, $insertedTableColumns[$i]->table_id);
        $this->assertEquals($exceptedTableColumns[$i]['column_name'], $insertedTableColumns[$i]->column_name);
        $this->assertEquals($exceptedTableColumns[$i]['column_name_alias'], $insertedTableColumns[$i]->column_name_alias);
        $this->assertEquals($exceptedTableColumns[$i]['length'], $insertedTableColumns[$i]->length);
        $this->assertEquals($exceptedTableColumns[$i]['maximum_number'], $insertedTableColumns[$i]->maximum_number);
        $this->assertEquals($exceptedTableColumns[$i]['decimal_part'], $insertedTableColumns[$i]->decimal_part);
        $this->assertEquals($exceptedTableColumns[$i]['data_type'], $insertedTableColumns[$i]->data_type);
        $this->assertEquals($exceptedTableColumns[$i]['validation'], $insertedTableColumns[$i]->validation);



        // DATASOURCE ********************
        //結果取得 get result
        $lastInsertedDatasource = Datasource::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasources' table
        $this->assertEquals($lastInsertedTable->id, $lastInsertedDatasource->table_id);
        $this->assertEquals($exceptedDatasource['datasource_name'], $lastInsertedDatasource->datasource_name);
        $this->assertEquals($exceptedDatasource['starting_row_number'], $lastInsertedDatasource->starting_row_number);



        // DATASOURCE_COLUMNS ********************
        //結果取得 get result
        $insertedDatasourceColumns = DatasourceColumns::where('datasource_id', $lastInsertedDatasource->id)->orderBy('id', 'asc')->get();

        // Check number of inserted record(s)
        $this->assertEquals(count($exceptedDatasourceColumns), $insertedDatasourceColumns->count());

        // Check table data of 'm_datasource_columns'
        $i = 0; // VARCHARであることを確認
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);


        //Clean up
        Schema::dropIfExists($exceptedTable['table_name']); // Deleting table technically deletes column.
    }

    /**
     * Input Pattern 2 - If excel header is empty and column name is not empty, return error
     * Excelで想定される入力パターン 組み合わせのチェック２
     * -> Excelヘッダ名なし・カラム名あり　→ エラーなし＆table_column のみ レコードができる
     */
    public function test_InputPattern_IfExcelHeaderIsEmptyAndColumnNameIsNotEmpty_ReturnError()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = '組み合わせチェック2_ヘッダ名無_カラム名有';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        //予想結果 excepted result
        $exceptedTable = [
            'table_name' => 'table_test_new',
            'table_name_alias' => 'テーブルテスト新規追加',
        ];
        $exceptedTableColumns = [
            [
                'column_name' => 'table_column1_BIGINT',
                'column_name_alias' => 'カラム１_BIGINT',
                'length' => 20,
                'maximum_number' => null,
                'decimal_part' => null,
                'data_type' => 'bigint',  //typeは小文字変換される
                'validation' => 'validation1',
            ],
            [
                'column_name' => 'table_column2_VARCHAR',
                'column_name_alias' => 'カラム２_VARCHAR',
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'data_type' => 'varchar',  //typeは小文字変換される
                'validation' => 'validation2',
            ],
        ];
        $exceptedDatasource = [
            'datasource_name'       => 'datasource_new',
            'starting_row_number'   => 2,
        ];
        $exceptedDatasourceColumns = [
            [
                'datasource_column_number' => 2,
                'datasource_column_name' => 'Excel列B_VARCHAR'
            ],
        ];


        // RESPONSE ********************
        $response
            ->assertStatus(200);

        // TABLE ********************
        //結果取得 get result
        $lastInsertedTable = Table::orderBy('id', 'DESC')->first();

        // Check table data of 'm_table'
        $this->assertEquals($exceptedTable['table_name'], $lastInsertedTable->table_name);
        $this->assertEquals($exceptedTable['table_name_alias'], $lastInsertedTable->table_name_alias);

        // Check table existence on Database
        $this->assertTrue(Schema::hasTable($exceptedTable['table_name']));


        // TABLE_COLUMNS ********************
        //結果取得 get result
        $insertedTableColumns = TableColumns::where('table_id', $lastInsertedTable->id)->orderBy('id', 'asc')->get();

        // Check number of inserted record(s)
        $this->assertEquals(count($exceptedTableColumns), $insertedTableColumns->count());

        // Check table data of 'm_table_columns'
        $i = 0;
        $this->assertEquals($lastInsertedTable->id, $insertedTableColumns[$i]->table_id);
        $this->assertEquals($exceptedTableColumns[$i]['column_name'], $insertedTableColumns[$i]->column_name);
        $this->assertEquals($exceptedTableColumns[$i]['column_name_alias'], $insertedTableColumns[$i]->column_name_alias);
        $this->assertEquals($exceptedTableColumns[$i]['length'], $insertedTableColumns[$i]->length);
        $this->assertEquals($exceptedTableColumns[$i]['maximum_number'], $insertedTableColumns[$i]->maximum_number);
        $this->assertEquals($exceptedTableColumns[$i]['decimal_part'], $insertedTableColumns[$i]->decimal_part);
        $this->assertEquals($exceptedTableColumns[$i]['data_type'], $insertedTableColumns[$i]->data_type);
        $this->assertEquals($exceptedTableColumns[$i]['validation'], $insertedTableColumns[$i]->validation);
        $i = 1;
        $this->assertEquals($lastInsertedTable->id, $insertedTableColumns[$i]->table_id);
        $this->assertEquals($exceptedTableColumns[$i]['column_name'], $insertedTableColumns[$i]->column_name);
        $this->assertEquals($exceptedTableColumns[$i]['column_name_alias'], $insertedTableColumns[$i]->column_name_alias);
        $this->assertEquals($exceptedTableColumns[$i]['length'], $insertedTableColumns[$i]->length);
        $this->assertEquals($exceptedTableColumns[$i]['maximum_number'], $insertedTableColumns[$i]->maximum_number);
        $this->assertEquals($exceptedTableColumns[$i]['decimal_part'], $insertedTableColumns[$i]->decimal_part);
        $this->assertEquals($exceptedTableColumns[$i]['data_type'], $insertedTableColumns[$i]->data_type);
        $this->assertEquals($exceptedTableColumns[$i]['validation'], $insertedTableColumns[$i]->validation);



        // DATASOURCE ********************
        //結果取得 get result
        $lastInsertedDatasource = Datasource::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasources' table
        $this->assertEquals($lastInsertedTable->id, $lastInsertedDatasource->table_id);
        $this->assertEquals($exceptedDatasource['datasource_name'], $lastInsertedDatasource->datasource_name);
        $this->assertEquals($exceptedDatasource['starting_row_number'], $lastInsertedDatasource->starting_row_number);



        // DATASOURCE_COLUMNS ********************
        //結果取得 get result
        $insertedDatasourceColumns = DatasourceColumns::where('datasource_id', $lastInsertedDatasource->id)->orderBy('id', 'asc')->get();

        // Check number of inserted record(s)
        $this->assertEquals(count($exceptedDatasourceColumns), $insertedDatasourceColumns->count());

        // Check table data of 'm_datasource_columns'
        $i = 0; // VARCHARであることを確認
        $this->assertEquals($insertedTableColumns[$i + 1]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);


        //Clean up
        Schema::dropIfExists($exceptedTable['table_name']); // Deleting table technically deletes column.
    }

    /**
     * Check error messages for m_table
     * エラーメッセージのチェック - m_tables でエラーが出た場合
     */
    public function test_ErrorMessageCheckForMTables()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'table必須チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 2])
            ->assertJsonFragment(['error_details' => [
                "テーブル名は必ず指定してください。",
                "テーブル名（別名）は必ず指定してください。"
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for m_datasource
     * エラーメッセージのチェック - m_datasources でエラーが出た場合
     */
    public function test_ErrorMessageCheckForMDatasources()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'datsource必須チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 2])
            ->assertJsonFragment(['error_details' => [
                "データソース名は必ず指定してください。",
                "開始行は必ず指定してください。",
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for table_columns
     * エラーメッセージのチェック - m_table_columns でエラーが出た場合
     */
    public function test_ErrorMessageCheckForMTableColumns()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'table_columns必須チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 1])
            ->assertJsonFragment(['error_details' => ["8行目 Excelヘッダ名「Excel列B_VARCHAR」 型は必ず指定してください。"]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for datasource_columns
     * エラーメッセージのチェック - m_datasource_columns でエラーが出た場合
     */
    public function test_ErrorMessageCheckForMDatasourceColumns()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'datasource_column必須チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 1])
            ->assertJsonFragment(['error_details' => ["7行目 Excelヘッダ名「Excel列A_BIGINT」 列番号は必ず指定してください。"]]);


        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for m_table and m_datasource
     * エラーメッセージのチェック - m_tables と m_datasources の両方にエラーが出た場合
     */
    public function test_ErrorMessageCheckForMTablesAndMDatasources()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'table&datasource必須チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 2])
            ->assertJsonFragment(['error_details' => [
                "テーブル名（別名）は必ず指定してください。",
                "開始行は必ず指定してください。"
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for m_table_columns and m_datasource_columns (they both have invalid values at SAME line)
     * エラーメッセージのチェック - m_table_columns と m_datasource_columns の両方にエラーが出た場合（同じ行）
     */
    public function test_ErrorMessageCheckForMTableColumnsAndMDatasourceColumns()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'tc&dc必須チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 2])
            ->assertJsonFragment(['error_details' => [
                "8行目 Excelヘッダ名「Excel列B_VARCHAR」 型は必ず指定してください。",
                "8行目 Excelヘッダ名「Excel列B_VARCHAR」 列番号は必ず指定してください。"
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for m_table_columns and m_datasource_columns (they both have invalid values (at OTHER lines)
     * エラーメッセージのチェック - m_table_columns と m_datasource_columns の両方にエラーが出た場合（異なる行）
     */
    public function test_ErrorMessageCheckForMTableColumnsAndMDatasourceColumnsAtMultipleLines()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'tc&dc必須チェック2';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 4])
            ->assertJsonFragment(['error_details' => [
                "8行目 Excelヘッダ名「Excel列B_DATE」 列番号は必ず指定してください。",
                "9行目 Excelヘッダ名「Excel列C_DATETIME」 テーブルカラム名（別名）は必ず指定してください。",
                "10行目 Excelヘッダ名「Excel列D_DECIMAL」 列番号は必ず指定してください。",
                "11行目 Excelヘッダ名「Excel列E_VARCHAR」 型がvarcharの場合、長さも指定してください。",
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for all 4 master tables (they all have invalid values)
     * エラーメッセージのチェック - m_系のテーブル（マスターテーブル）全てにエラーが出た場合（異なる行）
     */
    public function test_ErrorMessageCheckForAllMasterTables()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'all必須チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 6])
            ->assertJsonFragment(['error_details' => [
                "テーブル名（別名）は必ず指定してください。",
                "開始行は必ず指定してください。",
                "8行目 Excelヘッダ名「Excel列B_DATE」 列番号は必ず指定してください。",
                "9行目 Excelヘッダ名「Excel列C_DATETIME」 テーブルカラム名（別名）は必ず指定してください。",
                "10行目 Excelヘッダ名「Excel列D_DECIMAL」 列番号は必ず指定してください。",
                "11行目 Excelヘッダ名「Excel列E_VARCHAR」 型がvarcharの場合、長さも指定してください。",
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for all 4 master tables and some error variations
     * エラーメッセージのチェック - m_系のテーブル（マスターテーブル）全てにエラーが出た場合、いろんなエラーの複合
     */
    public function test_ErrorMessageCheckForAllMasterTablesAndErrorValiations()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'all複合チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 12])
            ->assertJsonFragment(['error_details' => [
                'テーブル名は、64文字以下で指定してください。',
                'テーブル名（別名）は必ず指定してください。',
                'データソース名は、255文字以下で指定してください。',
                '開始行には、1048576以下の数字を指定してください。',
                '7行目 Excelヘッダ名「Excel列A_BIGINT」 選択された型は正しくありません。',
                '7行目 Excelヘッダ名「Excel列A_BIGINT」 長さは整数で指定してください。',
                '9行目 Excelヘッダ名「Excel列C_DATETIME」 テーブルカラム名（別名）は必ず指定してください。',
                '10行目 テーブルカラム名「table_column4_DECIMAL」 全体長（長さ）は整数で指定してください。',
                '10行目 テーブルカラム名「table_column4_DECIMAL」 小数桁は整数で指定してください。',
                '11行目 Excelヘッダ名「Excelヘッダ名256文字56789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456」 型がvarcharの場合、長さも指定してください。',
                '11行目 Excelヘッダ名「Excelヘッダ名256文字56789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456」 Excelヘッダ名は、255文字以下で指定してください。',
                '11行目 Excelヘッダ名「Excelヘッダ名256文字56789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456」 列番号には、16384以下の数字を指定してください。',
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for duplicate table column names
     * エラーメッセージのチェック - カラム名が重複していた場合
     */
    public function test_ErrorMessageCheckForDupulicateTableColumNames()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'テーブルカラム名重複チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 2])
            ->assertJsonFragment(['error_details' => [
                '10行目 Excelヘッダ名「Excel列D_DECIMAL」 テーブルカラム名が他の列と重複しています。',
                '11行目 Excelヘッダ名「Excel列E_VARCHAR」 テーブルカラム名が他の列と重複しています。',
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for duplicate excel column index -> This is not error, it's normal case.
     * エラーメッセージのチェック - 列番号が重複していた場合 ※エラーではない
     */
    public function test_ErrorMessageCheckForDupulicateExcelColumnIndex()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = '列番号重複チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        //予想結果 excepted result
        $exceptedTable = [
            'table_name' => 'table_test_new',
            'table_name_alias' => 'テーブルテスト新規追加',
        ];
        $exceptedDatasourceColumns = [
            [
                'datasource_column_number' => 1,
                'datasource_column_name' => 'Excel列A_BIGINT'
            ],
            [
                'datasource_column_number' => 2,
                'datasource_column_name' => 'Excel列B_DATE'
            ],
            [
                'datasource_column_number' => 3,
                'datasource_column_name' => 'Excel列C_DATETIME'
            ],
            [
                'datasource_column_number' => 4,
                'datasource_column_name' => 'Excel列D_DECIMAL'
            ],
            [
                'datasource_column_number' => 1,    //This means column "A"
                'datasource_column_name' => 'Excel列E_VARCHAR'
            ],
        ];

        $response
            ->assertStatus(200);

        // DATASOURCE_COLUMNS ********************
        //結果取得 get result
        $lastInsertedDatasource = Datasource::orderBy('id', 'DESC')->first();
        $insertedDatasourceColumns = DatasourceColumns::where('datasource_id', $lastInsertedDatasource->id)->orderBy('id', 'asc')->get();

        // Check table data of 'm_datasource_columns'
        $i = 0;
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $i = 4;
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);

        //Clean up
        Schema::dropIfExists($exceptedTable['table_name']); // Deleting table technically deletes column.
    }

    /**
     * Check error messages for DB raw is over maximum size (65,535 byte)
     * エラーメッセージのチェック - DB の 1レコードの最大値、65,535 byteに収まっていない場合
     */
    public function test_ErrorMessageCheckForDBRawMaximumSize_over65535byte()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'over DB max row size';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 1])
            ->assertJsonFragment(['error_details' => [
                'データベースに設定できるカラム全体のサイズを超えています。VARCHARの長さを全体で 250 程度減らしてください。',
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * Check error messages for DB raw is within maximum size (65,535 byte)
     * エラーメッセージのチェック - DB の 1レコードの最大値、65,535 byteに収まっている場合
     */
    public function test_ErrorMessageCheckForDBRawMaximumSize_within65535byte()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = 'under DB max row size';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $exceptedTable = [
            'table_name' => 'table_test_new',
            'table_name_alias' => 'テーブルテスト新規追加',
        ];
        $exceptedTableColumns = [
            'BIGINT',
            'DATE',
            'DATETIME',
            'DECIMAL_10_2',
            'DECIMAL_65_30',
            'VARCHAR_255',
            'VARCHAR_256',
            'VARCHAR_4096',
            'VARCHAR_10000',
            'VARCHAR_1000',
        ];

        $response
            ->assertStatus(200);

        // Check table existence on Database
        $this->assertTrue(Schema::hasTable($exceptedTable['table_name']));
        // Check column existence of following table
        $this->assertTrue(Schema::hasColumns($exceptedTable['table_name'], $exceptedTableColumns));
    }

    /**
     * Check error messages for Additional Validation check
     * エラーメッセージのチェック - 追加バリデーションチェック
     */
    public function test_ErrorMessageCheckForAdditionalValidatons()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_new.xlsx');
        $originalName = 'GENS定義一括情報_新規追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Set parameters for posting
        $sheetName = '追加入力チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 12])
            ->assertJsonFragment(['error_details' => [
                '7行目 Excelヘッダ名「VARCHAR_MIN」 長さには、1以上の数字を指定してください。',
                '8行目 Excelヘッダ名「BIGINT_MIN」 長さには、1以上の数字を指定してください。',
                '9行目 Excelヘッダ名「DECIMAL_MIN」 全体長（長さ）には、1以上の数字を指定してください。',
                '9行目 Excelヘッダ名「DECIMAL_MIN」 小数桁には、1以上の数字を指定してください。',
                '10行目 Excelヘッダ名「VARCHAR_MAX」 varcharの場合、長さは16383以下で指定してください。',
                '11行目 Excelヘッダ名「BIGINT_MAX」 bigintの場合、長さは255以下で指定してください。',
                '12行目 Excelヘッダ名「DECIMAL_MAX」 decimalの場合、全体長（長さ）は65以下で指定してください。',
                '12行目 Excelヘッダ名「DECIMAL_MAX」 decimalの場合、小数桁は30以下で指定してください。',
                '13行目 Excelヘッダ名「DECIMAL_MAXLENGTH」 全体長（長さ）は小数桁よりも大きくしてください。',
                '14行目 Excelヘッダ名「COLUMN_NAME_HAS_DOT」 テーブルカラム名に利用できない文字が使われています。',
                '15行目 Excelヘッダ名「COLUMN_NAME_HAS_SPACE」 テーブルカラム名に利用できない文字が使われています。',
                'データベースに設定できるカラム全体のサイズを超えています。VARCHARの長さを全体で 1000 程度減らしてください。',
            ]]);

        // DBロールバックが効いていること
        $insertedTableCount = Table::count();
        $this->assertEquals(0, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(0, $insertedDatasourceCount);
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(0, $insertedTableColumnCount);
        $insertedDatasourceColumnCount = DatasourceColumns::count();
        $this->assertEquals(0, $insertedDatasourceColumnCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * m_table existence check before all validations, when user is not confirmed
     * Datasource の追加かどうか m_tableの存在チェック - ユーザー未確認
     */
    public function test_MTableExistenceCheckBeforeAllValidations()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_addDatasource.xlsx');
        $originalName = 'GENS定義一括情報_データソース追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Add Original table and datasource before posting
        $sheetName = '新規追加テーブル';
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);
        $response->assertStatus(200);

        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();


        // Set parameters for posting
        $sheetName = 'データソース追加';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);

        // Checking --------------------------------------
        $responseMsg = "同じテーブルに紐づくデータソースが他にもあります。\n";
        $responseMsg .= "テーブル名：table_test_new\n";
        $responseMsg .= "データソース名：datasource_new\n\n";
        $responseMsg .= "追加のデータソースとして設定しますか？";

        $response
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 30,
                    'message' => $responseMsg,
                ]
            );

        // DBに登録されていないこと
        $insertedTableCount = Table::count();
        $this->assertEquals(1, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(1, $insertedDatasourceCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * [ADD ONLY Datasource] m_table existence check before all validations, when user is not confirmed
     * Datasource の追加かどうか m_tableの存在チェック - ユーザー確認済み
     */
    public function test_MTableExistenceCheckBeforeAllValidations_UserConfirmed()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_addDatasource.xlsx');
        $originalName = 'GENS定義一括情報_データソース追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Add Original table and datasource before posting
        $sheetName = '新規追加テーブル';
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);
        $response->assertStatus(200);

        // get last inserted records
        $lastInsertedTable = Table::orderBy('id', 'DESC')->first();
        $insertedTableColumns = TableColumns::where('table_id', $lastInsertedTable->id)->orderBy('id', 'asc')->get();

        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();


        // Set parameters for posting
        $sheetName = 'データソース追加';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
            'add_only_datasource' => 'true',
        ]);

        // Checking --------------------------------------
        //予想結果 excepted result
        $exceptedTable = [
            'table_name' => 'table_test_new',
            'table_name_alias' => 'テーブルテスト新規追加',
        ];
        $exceptedDatasource = [
            'datasource_name'       => 'datasource_new2',
            'starting_row_number'   => 3,
        ];
        $exceptedDatasourceColumns = [
            [
                'datasource_column_number' => 1,
                'datasource_column_name' => 'Excel列A_BIGINT_2'
            ],
            [
                'datasource_column_number' => 2,
                'datasource_column_name' => 'Excel列B_DATE_2'
            ],
            [
                'datasource_column_number' => 3,
                'datasource_column_name' => 'Excel列C_DATETIME_2'
            ],
            [
                'datasource_column_number' => 4,
                'datasource_column_name' => 'Excel列D_DECIMAL_2'
            ],
            [
                'datasource_column_number' => 5,
                'datasource_column_name' => 'Excel列E_VARCHAR_2'
            ],
        ];


        // RESPONSE ********************
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['table_name' => $exceptedTable['table_name']])
            ->assertJsonFragment(['datasource_name' => $exceptedDatasource['datasource_name']]);


        // TABLE ********************
        //追加・更新されていないこと
        $insertedTableCount = Table::count();
        $this->assertEquals(1, $insertedTableCount);

        // TABLE_COLUMNS ********************
        //追加・更新されていないこと
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(5, $insertedTableColumnCount);


        // DATASOURCE ********************
        //結果取得 get result
        $lastInsertedDatasource = Datasource::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasources' table
        $this->assertEquals($lastInsertedTable->id, $lastInsertedDatasource->table_id);
        $this->assertEquals($exceptedDatasource['datasource_name'], $lastInsertedDatasource->datasource_name);
        $this->assertEquals($exceptedDatasource['starting_row_number'], $lastInsertedDatasource->starting_row_number);
        $this->assertTrue($lastInsertedDatasource->created_at != null);
        $this->assertTrue($lastInsertedDatasource->updated_at != null);
        $this->assertTrue($lastInsertedDatasource->created_by == null);
        $this->assertTrue($lastInsertedDatasource->updated_by == null);



        // DATASOURCE_COLUMNS ********************
        //結果取得 get result
        $insertedDatasourceColumns = DatasourceColumns::where('datasource_id', $lastInsertedDatasource->id)->orderBy('id', 'asc')->get();

        // Check table data of 'm_datasource_columns'
        $i = 0;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 1;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 2;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 3;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 4;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);


        //Clean up
        Schema::dropIfExists($exceptedTable['table_name']); // Deleting table technically deletes column.
    }

    /**
     * [ADD ONLY Datasource] Input Pattern 1 - If excel header is not Empty and column name is empty, ignore target row
     * Excelで想定される入力パターン 組み合わせのチェック１
     * -> Excelヘッダ名あり・カラム名なし　→ エラーなし＆datasource_columnにレコードはできない
     */
    public function test_AddOnlyDatasource_InputPattern_IfExcelHeaderIsNotEmptyAndColumnNameIsEmpty_IgnoreTargetRow()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_addDatasource.xlsx');
        $originalName = 'GENS定義一括情報_データソース追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Add Original table and datasource before posting
        $sheetName = '新規追加テーブル';
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);
        $response->assertStatus(200);

        // get last inserted records
        $lastInsertedTable = Table::orderBy('id', 'DESC')->first();
        $insertedTableColumns = TableColumns::where('table_id', $lastInsertedTable->id)->orderBy('id', 'asc')->get();

        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();


        // Set parameters for posting
        $sheetName = '組み合わせチェック1_ヘッダ名有_カラム名無';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
            'add_only_datasource' => 'true',
        ]);

        // Checking --------------------------------------
        //予想結果 excepted result
        $exceptedTable = [
            'table_name' => 'table_test_new',
            'table_name_alias' => 'テーブルテスト新規追加',
        ];
        $exceptedDatasource = [
            'datasource_name'       => 'datasource_new2',
            'starting_row_number'   => 3,
        ];
        $exceptedDatasourceColumns = [
            [
                'datasource_column_number' => 1,
                'datasource_column_name' => 'Excel列A_BIGINT_2'
            ],
            [
                'datasource_column_number' => 2,
                'datasource_column_name' => 'Excel列B_DATE_2'
            ],
            // [    // 取り込まない
            //     'datasource_column_number' => 3,
            //     'datasource_column_name' => 'Excel列C_DATETIME_2'
            // ],
            [
                'datasource_column_number' => 4,
                'datasource_column_name' => 'Excel列D_DECIMAL_2'
            ],
            [
                'datasource_column_number' => 5,
                'datasource_column_name' => 'Excel列E_VARCHAR_2'
            ],
        ];


        // RESPONSE ********************
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['table_name' => $exceptedTable['table_name']])
            ->assertJsonFragment(['datasource_name' => $exceptedDatasource['datasource_name']]);


        // TABLE ********************
        //追加・更新されていないこと
        $insertedTableCount = Table::count();
        $this->assertEquals(1, $insertedTableCount);

        // TABLE_COLUMNS ********************
        //追加・更新されていないこと
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(5, $insertedTableColumnCount);


        // DATASOURCE ********************
        //結果取得 get result
        $lastInsertedDatasource = Datasource::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasources' table
        $this->assertEquals($lastInsertedTable->id, $lastInsertedDatasource->table_id);
        $this->assertEquals($exceptedDatasource['datasource_name'], $lastInsertedDatasource->datasource_name);
        $this->assertEquals($exceptedDatasource['starting_row_number'], $lastInsertedDatasource->starting_row_number);
        $this->assertTrue($lastInsertedDatasource->created_at != null);
        $this->assertTrue($lastInsertedDatasource->updated_at != null);
        $this->assertTrue($lastInsertedDatasource->created_by == null);
        $this->assertTrue($lastInsertedDatasource->updated_by == null);



        // DATASOURCE_COLUMNS ********************
        //結果取得 get result
        $insertedDatasourceColumns = DatasourceColumns::where('datasource_id', $lastInsertedDatasource->id)->orderBy('id', 'asc')->get();

        // Check table data of 'm_datasource_columns'
        $this->assertEquals(4, count($insertedDatasourceColumns));
        $i = 0;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 1;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 2;
        $this->assertEquals($insertedTableColumns[$i + 1]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 3;
        $this->assertEquals($insertedTableColumns[$i + 1]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);


        //Clean up
        Schema::dropIfExists($exceptedTable['table_name']); // Deleting table technically deletes column.
    }

    /**
     * [ADD ONLY Datasource] Input Pattern 2 - If excel header is Empty and column name is not empty, ignore target row
     * Excelで想定される入力パターン 組み合わせのチェック２
     * -> Excelヘッダ名なし・カラム名あり　→ エラーなし＆datasource_columnにレコードはできない
     */
    public function test_AddOnlyDatasource_InputPattern_IfExcelHeaderIsEmptyAndColumnNameIsNotEmpty_IgnoreTargetRow()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_addDatasource.xlsx');
        $originalName = 'GENS定義一括情報_データソース追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Add Original table and datasource before posting
        $sheetName = '新規追加テーブル';
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);
        $response->assertStatus(200);

        // get last inserted records
        $lastInsertedTable = Table::orderBy('id', 'DESC')->first();
        $insertedTableColumns = TableColumns::where('table_id', $lastInsertedTable->id)->orderBy('id', 'asc')->get();

        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();


        // Set parameters for posting
        $sheetName = '組み合わせチェック2_ヘッダ名無_カラム名有';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
            'add_only_datasource' => 'true',
        ]);

        // Checking --------------------------------------
        //予想結果 excepted result
        $exceptedTable = [
            'table_name' => 'table_test_new',
            'table_name_alias' => 'テーブルテスト新規追加',
        ];
        $exceptedDatasource = [
            'datasource_name'       => 'datasource_new2',
            'starting_row_number'   => 3,
        ];
        $exceptedDatasourceColumns = [
            [
                'datasource_column_number' => 1,
                'datasource_column_name' => 'Excel列A_BIGINT_2'
            ],
            [
                'datasource_column_number' => 2,
                'datasource_column_name' => 'Excel列B_DATE_2'
            ],
            // [    // 取り込まない
            //     'datasource_column_number' => 3,
            //     'datasource_column_name' => 'Excel列C_DATETIME_2'
            // ],
            [
                'datasource_column_number' => 4,
                'datasource_column_name' => 'Excel列D_DECIMAL_2'
            ],
            [
                'datasource_column_number' => 5,
                'datasource_column_name' => 'Excel列E_VARCHAR_2'
            ],
        ];


        // RESPONSE ********************
        $response
            ->assertStatus(200)
            ->assertJsonFragment(['table_name' => $exceptedTable['table_name']])
            ->assertJsonFragment(['datasource_name' => $exceptedDatasource['datasource_name']]);


        // TABLE ********************
        //追加・更新されていないこと
        $insertedTableCount = Table::count();
        $this->assertEquals(1, $insertedTableCount);

        // TABLE_COLUMNS ********************
        //追加・更新されていないこと
        $insertedTableColumnCount = TableColumns::count();
        $this->assertEquals(5, $insertedTableColumnCount);


        // DATASOURCE ********************
        //結果取得 get result
        $lastInsertedDatasource = Datasource::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasources' table
        $this->assertEquals($lastInsertedTable->id, $lastInsertedDatasource->table_id);
        $this->assertEquals($exceptedDatasource['datasource_name'], $lastInsertedDatasource->datasource_name);
        $this->assertEquals($exceptedDatasource['starting_row_number'], $lastInsertedDatasource->starting_row_number);
        $this->assertTrue($lastInsertedDatasource->created_at != null);
        $this->assertTrue($lastInsertedDatasource->updated_at != null);
        $this->assertTrue($lastInsertedDatasource->created_by == null);
        $this->assertTrue($lastInsertedDatasource->updated_by == null);



        // DATASOURCE_COLUMNS ********************
        //結果取得 get result
        $insertedDatasourceColumns = DatasourceColumns::where('datasource_id', $lastInsertedDatasource->id)->orderBy('id', 'asc')->get();

        // Check table data of 'm_datasource_columns'
        $this->assertEquals(count($exceptedDatasourceColumns), count($insertedDatasourceColumns));
        $i = 0;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 1;
        $this->assertEquals($insertedTableColumns[$i]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 2;
        $this->assertEquals($insertedTableColumns[$i + 1]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);
        $i = 3;
        $this->assertEquals($insertedTableColumns[$i + 1]->id, $insertedDatasourceColumns[$i]->table_column_id);
        $this->assertEquals($lastInsertedDatasource->id, $insertedDatasourceColumns[$i]->datasource_id);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_number'], $insertedDatasourceColumns[$i]->datasource_column_number);
        $this->assertEquals($exceptedDatasourceColumns[$i]['datasource_column_name'], $insertedDatasourceColumns[$i]->datasource_column_name);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_at != null);
        $this->assertTrue($insertedDatasourceColumns[$i]->created_by == null);
        $this->assertTrue($insertedDatasourceColumns[$i]->updated_by == null);


        //Clean up
        Schema::dropIfExists($exceptedTable['table_name']); // Deleting table technically deletes column.
    }


    /**
     * [ADD ONLY Datasource] Check error messages for existence for table column
     * エラーメッセージのチェック - 対応するテーブルカラムが存在しない場合
     */
    public function test_AddOnlyDatasource_ErrorMessageCheckForExistenceForTablColumn()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_addDatasource.xlsx');
        $originalName = 'GENS定義一括情報_データソース追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Add Original table and datasource before posting
        $sheetName = '新規追加テーブル';
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);
        $response->assertStatus(200);

        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();


        // Set parameters for posting
        $sheetName = 'テーブルカラムが存在しない';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
            'add_only_datasource' => 'true',
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 2])
            ->assertJsonFragment(['error_details' => [
                '8行目 Excelヘッダ名「Excel列B_DATE_2」 指定されたテーブルカラム名は該当のテーブルに存在しません。',
                '10行目 Excelヘッダ名「Excel列D_DECIMAL_2」 指定されたテーブルカラム名は該当のテーブルに存在しません。',
            ]]);


        // DBに登録されていないこと
        $insertedTableCount = Table::count();
        $this->assertEquals(1, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(1, $insertedDatasourceCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * [ADD ONLY Datasource] Check error messages for all 2 master tables (they all have invalid values)
     * エラーメッセージのチェック - m_datasource, m_datasource_columns 全てにエラーが出た場合（異なる行）
     */
    public function test_AddOnlyDatasource_ErrorMessageCheckForAllMasterDatasourceTables()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_addDatasource.xlsx');
        $originalName = 'GENS定義一括情報_データソース追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Add Original table and datasource before posting
        $sheetName = '新規追加テーブル';
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);
        $response->assertStatus(200);

        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();


        // Set parameters for posting
        $sheetName = 'all必須チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
            'add_only_datasource' => 'true',
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 3])
            ->assertJsonFragment(['error_details' => [
                "開始行は必ず指定してください。",
                "8行目 Excelヘッダ名「Excel列B_DATE_2」 列番号は必ず指定してください。",
                "11行目 Excelヘッダ名「Excel列E_VARCHAR_2」 列番号は必ず指定してください。",
            ]]);


        // DBに登録されていないこと
        $insertedTableCount = Table::count();
        $this->assertEquals(1, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(1, $insertedDatasourceCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }

    /**
     * [ADD ONLY Datasource] Check error messages for all 2 master tables and some error variations
     * エラーメッセージのチェック - m_datasource / m_datsource_column にエラーが出た場合、いろんなエラーの複合
     */
    public function test_AddOnlyDatasource_ErrorMessageCheckForAllMasterTablesAndErrorValiations()
    {
        Storage::fake('public');

        // preparation --------------------------------------
        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/GendsBulkDefinition_addDatasource.xlsx');
        $originalName = 'GENS定義一括情報_データソース追加.xlsx';
        $mimeType = null;
        $error = null;
        $test = true;

        // Add Original table and datasource before posting
        $sheetName = '新規追加テーブル';
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
        ]);
        $response->assertStatus(200);

        // get current number of table count
        $tableCount = $this->countNumberOfTableOnDatabase();


        // Set parameters for posting
        $sheetName = 'all複合チェック';

        // Executing --------------------------------------
        $response = $this->post('/api/v1/definition-bulk', [
            'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
            'sheet_name' => $sheetName,
            'add_only_datasource' => 'true',
        ]);

        // Checking --------------------------------------
        $response
            ->assertStatus(400)
            ->assertJsonFragment(['error_code' => 20])
            ->assertJsonFragment(["error_details_count" => 6])
            ->assertJsonFragment(['error_details' => [
                'データソース名の値は既に存在しています。',
                '開始行は必ず指定してください。',
                '8行目 Excelヘッダ名「Excel列B_DATE_2」 指定されたテーブルカラム名は該当のテーブルに存在しません。',
                "9行目 Excelヘッダ名「Excel列C_DATETIME_2」 列番号は必ず指定してください。",
                '11行目 Excelヘッダ名「Excelヘッダ名256文字56789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456」 Excelヘッダ名は、255文字以下で指定してください。',
                '11行目 Excelヘッダ名「Excelヘッダ名256文字56789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456」 列番号には、16384以下の数字を指定してください。',
            ]]);


        // DBに登録されていないこと
        $insertedTableCount = Table::count();
        $this->assertEquals(1, $insertedTableCount);
        $insertedDatasourceCount = Datasource::count();
        $this->assertEquals(1, $insertedDatasourceCount);
        $this->assertEquals($tableCount, $this->countNumberOfTableOnDatabase());
    }
}
