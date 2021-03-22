<?php

namespace Tests\Feature\API;

use App\Models\DatasourceColumns;
use App\Models\Table;
use App\Models\TableColumns;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class APITableColumnControllerAddOperationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // m_table_columns を論理削除のデータを含めて全件削除
        TableColumns::query()->truncate();

        // m_tables にデータをセット
        TableColumns::create(
            [
                'id' => 1,
                'table_id' => 1,
                'column_name' => "Column name 1",
                'column_name_alias' => "Column name alias 1",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        );
        TableColumns::create(
            [
                'id' => 2,
                'table_id' => 1,
                'column_name' => "Column name 2",
                'column_name_alias' => "Column name alias 2",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        );
        TableColumns::create(
            [
                'id' => 3,
                'table_id' => 1,
                'column_name' => "Column name 3",
                'column_name_alias' => "Column name alias 3",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        );
        TableColumns::create(
            [
                'id' => 4,
                'table_id' => 1,
                'column_name' => "Column name 4",
                'column_name_alias' => "Column name alias 4",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        );
        TableColumns::create(
            [
                'id' => 5,
                'table_id' => 1,
                'column_name' => "Column name 5",
                'column_name_alias' => "Column name alias 5",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        );

        //Delete newly created if exists on DB.
        Schema::dropIfExists('table_name');
    }

    protected function tearDown(): void
    {
        // m_tables を論理削除のデータを含めて全件削除
        Table::query()->truncate();
        // m_table_columns を論理削除のデータを含めて全件削除
        TableColumns::query()->truncate();
        DatasourceColumns::query()->delete();
        //Delete newly created if exists on DB.
        Schema::dropIfExists('table_name');
        parent::tearDown();
    }

    /**
     *  Add New Table column Test
     */
    public function testAddNewItemOnMTablesColumnTable()
    {
        Log::info(__FUNCTION__);

        //Preparation
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);
        $addTableResponse->assertOk();

        // Add logically deleted data
        TableColumns::create(
            [
                'id' => 6,
                'table_id' => $addTableResponse->original['table']['id'],
                'column_name' => "columnName",
                'column_name_alias' => "columnNameAlias",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => '2020-02-01 00:00:00',
            ]
        );

        //Execute
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "varchar",
            'length' => 255,
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => 'required',
        ];

        $response = $this->post('api/v1/add/table-columns', $postData);

        // Check Response status and value
        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'created' => true,
                ]
            );
        $this->assertEquals($response->original['column']['table_id'], $postData['table_id']);
        $this->assertEquals($response->original['column']['column_name'], $postData['column_name']);
        $this->assertEquals($response->original['column']['column_name_alias'], $postData['column_name_alias']);
        $this->assertEquals($response->original['column']['length'], $postData['length']);
        $this->assertEquals($response->original['column']['maximum_number'], $postData['maximum_number']);
        $this->assertEquals($response->original['column']['decimal_part'], $postData['decimal_part']);
        $this->assertEquals($response->original['column']['data_type'], $postData['data_type']);
        $this->assertEquals($response->original['column']['validation'], $postData['validation']);

        // Check table data of 'm_table_columns'
        $lastInsertedRecord = TableColumns::orderBy('id', 'DESC')->first();
        $this->assertEquals($postData['table_id'], $lastInsertedRecord->table_id);
        $this->assertEquals($postData['column_name'], $lastInsertedRecord->column_name);
        $this->assertEquals($postData['column_name_alias'], $lastInsertedRecord->column_name_alias);
        $this->assertEquals($postData['length'], $lastInsertedRecord->length);
        $this->assertEquals($postData['maximum_number'], $lastInsertedRecord->maximum_number);
        $this->assertEquals($postData['decimal_part'], $lastInsertedRecord->decimal_part);
        $this->assertEquals($postData['data_type'], $lastInsertedRecord->data_type);
        $this->assertEquals($postData['validation'], $lastInsertedRecord->validation);
        $this->assertTrue($lastInsertedRecord->created_at != null);
        $this->assertTrue($lastInsertedRecord->updated_at != null);
        $this->assertTrue($lastInsertedRecord->created_by == null);
        $this->assertTrue($lastInsertedRecord->updated_by == null);

        // Check existence on DB
        $this->assertTrue(Schema::hasColumn($tablePostData['table_name'], $postData['column_name']));

        // Check total column number
        $columnsCount = sizeof(Schema::getColumnListing($tablePostData['table_name']));
        $this->assertEquals(5, $columnsCount); // Table Create API add 4 columns. So 4+1 = 5
        //Check column type from Database
        $this->assertEquals('string', Schema::getColumnType($tablePostData['table_name'], $postData['column_name']));
        //check column length
        $con = DB::connection();
        $column = $con->getDoctrineColumn($tablePostData['table_name'], $postData['column_name']);
        $this->assertEquals($postData['length'], $column->getLength());
        //check nullable
        $this->assertEquals(false, $column->getNotnull());
        //check default
        $this->assertEquals(false, $column->getDefault());

        //Clean up
        Schema::dropIfExists($tablePostData['table_name']); // Deleting table technically deletes column.
    }

    /**
     *  Add New Table column Test for bigint data type
     */
    public function testAddNewItemOnMTablesColumnTableForBigintDataType()
    {
        Log::info(__FUNCTION__);

        //Preparation
        //Execute
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "bigint",
            'length' => 12,
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => null,
        ];

        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking
        $lastInsertedRecord = TableColumns::orderBy('id', 'DESC')->first();

        // Check table data of 'm_table_columns'
        $this->assertEquals($postData['data_type'], $lastInsertedRecord->data_type);
        $this->assertEquals($postData['length'], $lastInsertedRecord->length);
        //Check column type from Database
        $this->assertEquals($postData['data_type'], Schema::getColumnType($tablePostData['table_name'], $postData['column_name']));
        //Bigint doesn't support any length.
        $con = DB::connection();
        $column = $con->getDoctrineColumn($tablePostData['table_name'], $postData['column_name']);
        //check nullable
        $this->assertEquals(false, $column->getNotnull());
        //check default
        $this->assertEquals(false, $column->getDefault());

        //Check response
        $this->assertEquals($response->original['column']['length'], $postData['length']);
        //Clean up
        Schema::dropIfExists($tablePostData['table_name']); // Deleting table technically deletes column.
    }

    /**
     *  Add New Table column Test for datetime data type
     */
    public function testAddNewItemOnMTablesColumnTableForDatetimeDataType()
    {
        Log::info(__FUNCTION__);

        //Preparation

        //Execute
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "datetime",
            'length' => null,
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => null,
        ];

        $this->post('api/v1/add/table-columns', $postData);

        //checking
        $lastInsertedRecord = TableColumns::orderBy('id', 'DESC')->first();

        // Check table data of 'm_table_columns'
        $this->assertEquals($postData['data_type'], $lastInsertedRecord->data_type);
        //Check column type from Database
        $this->assertEquals($postData['data_type'], Schema::getColumnType($tablePostData['table_name'], $postData['column_name']));
        //check nullable
        $con = DB::connection();
        $column = $con->getDoctrineColumn($tablePostData['table_name'], $postData['column_name']);
        $this->assertEquals(false, $column->getNotnull());
        //check default
        $this->assertEquals(false, $column->getDefault());

        //Clean up
        Schema::dropIfExists($tablePostData['table_name']); // Deleting table technically deletes column.
    }

    /**
     *  Add New Table column Test for date data type
     */
    public function testAddNewItemOnMTablesColumnTableForDateDataType()
    {
        Log::info(__FUNCTION__);

        //Preparation
        //Execute
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "date",
            'length' => null,
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => null,
        ];

        $this->post('api/v1/add/table-columns', $postData);

        //checking
        $lastInsertedRecord = TableColumns::orderBy('id', 'DESC')->first();

        // Check table data of 'm_table_columns'
        $this->assertEquals($postData['data_type'], $lastInsertedRecord->data_type);
        //Check column type from Database
        $this->assertEquals($postData['data_type'], Schema::getColumnType($tablePostData['table_name'], $postData['column_name']));
        //check nullable
        $con = DB::connection();
        $column = $con->getDoctrineColumn($tablePostData['table_name'], $postData['column_name']);
        $this->assertEquals(false, $column->getNotnull());
        //check default
        $this->assertEquals(false, $column->getDefault());

        //Clean up
        Schema::dropIfExists($tablePostData['table_name']); // Deleting table technically deletes column.
    }

    /**
     *  Add New Table column Test for decimal data type
     */
    public function testAddNewItemOnMTablesColumnTableForDecimalDataType()
    {
        Log::info(__FUNCTION__);

        //Preparation
        //Execute
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "decimal",
            'length' => null,
            'maximum_number' => 9,
            'decimal_part' => 3,
            'validation' => null,
        ];

        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking
        $lastInsertedRecord = TableColumns::orderBy('id', 'DESC')->first();

        // Check table data of 'm_table_columns'
        $this->assertEquals($postData['data_type'], $lastInsertedRecord->data_type);
        $this->assertEquals($postData['length'], $lastInsertedRecord->length);
        $this->assertEquals($postData['maximum_number'], $lastInsertedRecord->maximum_number);
        $this->assertEquals($postData['decimal_part'], $lastInsertedRecord->decimal_part);
        //Check column type from Database
        $this->assertEquals($postData['data_type'], Schema::getColumnType($tablePostData['table_name'], $postData['column_name']));

        //check column max_number and decimal part
        $con = DB::connection();
        $column = $con->getDoctrineColumn($tablePostData['table_name'], $postData['column_name']);
        $this->assertEquals($postData['maximum_number'], $column->getPrecision());
        $this->assertEquals($postData['decimal_part'], $column->getScale());
        //check nullable
        $this->assertEquals(false, $column->getNotnull());
        //check default
        $this->assertEquals(false, $column->getDefault());

        //Check response value
        $this->assertEquals($response->original['column']['maximum_number'], $postData['maximum_number']);
        $this->assertEquals($response->original['column']['decimal_part'], $postData['decimal_part']);

        //Clean up
        Schema::dropIfExists($tablePostData['table_name']); // Deleting table technically deletes column.
    }

    /**
     * Validator error - all parameters required - If parameters are not existed
     */
    public function testValidatorForRequired()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    "table_id" => ["テーブルIDは必ず指定してください。"],
                    "column_name" => ["テーブルカラム名は必ず指定してください。"],
                    "column_name_alias" => ["テーブルカラム名（別名）は必ず指定してください。"],
                    "data_type" => ["型は必ず指定してください。"],
                ]
            );
    }

    /**
     * Validator error - all parameters required - If parameters are empty
     */
    public function testValidatorForRequiredForEmpty()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'table_id'              => '',
            'column_name'           => '',
            'column_name_alias'     => '',
            'data_type'             => '',
            'length'                => null,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    "table_id" => ["テーブルIDは必ず指定してください。"],
                    "column_name" => ["テーブルカラム名は必ず指定してください。"],
                    "column_name_alias" => ["テーブルカラム名（別名）は必ず指定してください。"],
                    "data_type" => ["型は必ず指定してください。"],
                ]
            );
    }

    /**
     * Validator error - requested data_type is not in the defined list
     */
    public function testValidatorForDataTypeMustBeIncludedInTheList()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "int",   //int is not in the list, use bigint usually
            'length' => 255,
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => 'required',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);

        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "data_type" => ["選択された型は正しくありません。"],
            ]);
    }

    /**
     * Validator error - length is required if data_type is varchar
     */
    public function testValidatorForLegnthRequiredIfDataTypeIsVarchar()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "varchar",
            'length' => null, //required
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => 'required',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);

        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    "length" => ["型がvarcharの場合、長さも指定してください。"],
                ]
            );
    }

    /**
     * Validator error - length is required if data_type is bigint
     */
    public function testValidatorForLegnthRequiredIfDataTypeIsBigint()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "bigint",
            'length' => null, //required
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => 'required',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);

        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    "length" => ["型がbigintの場合、長さも指定してください。"],
                ]
            );
    }

    /**
     * Validator error - maximum_number and decimal_part are required if data_type is decimal
     */
    public function testValidatorFor2ColumnsRequiredIfDataTypeIsDecimal()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "decimal",
            'length' => null,
            'maximum_number' => null, //required
            'decimal_part' => null, //required
            'validation' => 'required',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);

        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    "maximum_number" => ["型がdecimalの場合、全体長（長さ）も指定してください。"],
                    "decimal_part" => ["型がdecimalの場合、小数桁も指定してください。"],
                ]
            );
    }

    /**
     * Validator error - if specific table is not existed
     */
    public function testValidatorForTableShouldExist()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'table_id'              => 6,
            'column_name'           => 'a',
            'column_name_alias'     => 'a',
            'data_type'             => 'a',
            'length'                => null,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    "table_id" => ["選択されたテーブルIDは正しくありません。"],
                ]
            );
    }

    /**
     * Validator error - the table column name should be unique
     */
    public function testValidatorForTableColumnNameShouldBeUnique()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'table_id'              => 1,
            'column_name'           => 'Column name 1',
            'column_name_alias'     => 'a',
            'data_type'             => 'a',
            'length'                => null,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    "column_name" => ["テーブルカラム名の値は既に存在しています。"],
                ]
            );
    }

    /**
     * Validator error - the length should be grater than 0 (Varchar)
     */
    public function testValidatorForVarcharLengthShouldBeGraterThan0()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "columnName",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'varchar',
            'length'                => 0,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "length" => ["長さには、1以上の数字を指定してください。"],
            ]);
    }

    /**
     * Validator error - the length should be grater than 0 (Bigint)
     */
    public function testValidatorForBigintLengthShouldBeGraterThan0()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "columnName",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'bigint',
            'length'                => 0,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "length" => ["長さには、1以上の数字を指定してください。"],
            ]);
    }

    /**
     * Validator error - the length should be grater than 0 (Decimal)
     */
    public function testValidatorForDecimalLengthShouldBeGraterThan0()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "columnName",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'decimal',
            'length'                => null,
            'maximum_number'        => 0,
            'decimal_part'          => 0,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "maximum_number" => ["全体長（長さ）には、1以上の数字を指定してください。"],
                "decimal_part" => ["小数桁には、1以上の数字を指定してください。"],
            ]);
    }

    /**
     * Validator error - the length should be less than 16383 (Varchar)
     */
    public function testValidatorForVarcharLengthShouldBeLessThan16383()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "columnName",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'varchar',
            'length'                => 16384,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "length" => ["varcharの場合、長さは16383以下で指定してください。"],
            ]);
    }

    /**
     * Validator error - the length should be less than 255 (Bigint)
     */
    public function testValidatorForVarcharLengthShouldBeLessThan255()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "columnName",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'bigint',
            'length'                => 256,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "length" => ["bigintの場合、長さは255以下で指定してください。"],
            ]);
    }

    /**
     * Validator error - the length should be less than 65 and 30 (Decimal)
     */
    public function testValidatorForDecimalLengthShouldBeLessThan65and30()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "columnName",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'decimal',
            'length'                => null,
            'maximum_number'        => 66,
            'decimal_part'          => 31,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "maximum_number" => ["decimalの場合、全体長（長さ）は65以下で指定してください。"],
                "decimal_part" => ["decimalの場合、小数桁は30以下で指定してください。"],
            ]);
    }

    /**
     * Validator error - the decimal maximum number should be larger than decimal part
     */
    public function testValidatorForDecimaMaximuNumberShouldBeLargerThanDecimalPart()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "columnName",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'decimal',
            'length'                => null,
            'maximum_number'        => 2,
            'decimal_part'          => 3,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "maximum_number" => ["全体長（長さ）は小数桁よりも大きくしてください。"]
            ]);
    }

    /**
     * Validator error - table column name doesn't allow to use period (.)
     */
    public function testValidatorForTableColumnNameDoesNotAllowToUsePeriod()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "column.name",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'varchar',
            'length'                => 10,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "column_name" => ["テーブルカラム名に利用できない文字が使われています。"]
            ]);
    }

    /**
     * Validator error - table column name doesn't allow to use space
     */
    public function testValidatorForTableColumnNameDoesNotAllowToUseSpace()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        //Execute -----------------------------
        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name'           => "column name",
            'column_name_alias'     => "columnNameAlias",
            'data_type'             => 'varchar',
            'length'                => 10,
            'maximum_number'        => null,
            'decimal_part'          => null,
            'validation'            => '',
        ];
        $response = $this->post('api/v1/add/table-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table_columns' table
        $tableColumnsDataCount = TableColumns::count();
        $this->assertEquals(5, $tableColumnsDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "column_name" => ["テーブルカラム名に利用できない文字が使われています。"]
            ]);
    }
}
