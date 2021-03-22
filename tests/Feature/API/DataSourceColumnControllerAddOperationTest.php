<?php

namespace Tests\Feature\API;

use App\Models\Table;
use App\Models\TableColumns;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Log;
use Tests\TestCase;

class DataSourceColumnControllerAddOperationTest extends TestCase
{
    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // truncate all using tables
        Table::query()->truncate();
        TableColumns::query()->truncate();
        DataSource::query()->truncate();
        DatasourceColumns::query()->truncate();

        // insert initialized data for m_tables / m_table_columns / m_datasources
        Table::insert([
            [
                'id' => 1,
                'table_name' => "table_name_1",
                'table_name_alias' => "Table Name Alias 1",
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
            ]
        ]);

        TableColumns::insert([
            [
                'id' => 1,
                'table_id' => 1,
                'column_name' => "column_name_1",
                'column_name_alias' => "Column name alias 1",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
            ],
        ]);

        Datasource::insert([
            [
                'id' => 1,
                'datasource_name' => 'Datasource name 1',
                'table_id' => 1,
                'starting_row_number' => 2,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        ]);

        //Insert soft deleted record into m_datasource_columns
        DatasourceColumns::insert([
            [
                'id' => 1,
                'datasource_id'             => 1,
                'datasource_column_number'  => 1,
                'datasource_column_name'    => 'datasource column name',
                'table_column_id'           => 1,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => '2020-01-02 00:00:00', // Soft deleted
            ]
        ]);
    }

    /**
     * Tear down
     *
     * @return void
     */
    protected function tearDown(): void
    {

        // truncate all used table
        Table::query()->truncate();
        TableColumns::query()->truncate();
        DataSource::query()->truncate();
        DatasourceColumns::query()->truncate();

        parent::tearDown();
    }

    /**
     *  Add New Datasource column Test
     */
    public function testAddNewItemOnDatasourceColumnTable()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------
        $lastInsertedRecord = DatasourceColumns::orderBy('id', 'DESC')->first();

        // check response data
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                //inserted data
                'id' => $lastInsertedRecord->id,
                'table_column_id' => $postData['table_column_id'],
                'datasource_id' => $postData['datasource_id'],
                'datasource_column_number' => $postData['datasource_column_number'],
                'datasource_column_name' => $postData['datasource_column_name'],
            ])
            ->assertJsonFragment([
                //additional data
                'DataSourceName' => 'Datasource name 1',
                'DataSourceTableName' => 'table_name_1',
                'ColumnName' => 'column_name_1',
            ])
            ->assertJsonFragment([
                //related data
                "table_definition" => [
                    "id" => 1,
                    "table_id" => 1,
                    "column_name" => "column_name_1",
                    "column_name_alias" => "Column name alias 1",
                    "data_type" => "varchar",
                    "length" => 255,
                    "maximum_number" => null,
                    "decimal_part" => null,
                    "validation" => null,
                    "created_at" => "2020-01-01 00:00:00",
                    "created_by" => null,
                    "updated_at" => "2020-01-01 00:00:00",
                    "updated_by" => null,
                    "deleted_at" => null,
                ],
                "data_source" => [
                    "id" => 1,
                    "datasource_name" => "Datasource name 1",
                    "table_id" => 1,
                    "starting_row_number" => 2,
                    "created_at" => "2020-01-01 00:00:00",
                    "created_by" => null,
                    "updated_at" => "2020-01-01 00:00:00",
                    "updated_by" => null,
                    "deleted_at" => null,
                    "tables" => [
                        "id" => 1,
                        "table_name" => "table_name_1",
                        "table_name_alias" => "Table Name Alias 1",
                        "created_at" => "2020-01-01 00:00:00",
                        "created_by" => null,
                        "updated_at" => "2020-01-01 00:00:00",
                        "updated_by" => null,
                        "deleted_at" => null,
                    ],
                ],
            ]);

        // Check table data of 'm_datasource_columns'
        $this->assertEquals($postData['table_column_id'], $lastInsertedRecord->table_column_id);
        $this->assertEquals($postData['datasource_id'], $lastInsertedRecord->datasource_id);
        $this->assertEquals($postData['datasource_column_number'], $lastInsertedRecord->datasource_column_number);
        $this->assertEquals($postData['datasource_column_name'], $lastInsertedRecord->datasource_column_name);
        $this->assertTrue($lastInsertedRecord->created_at != null);
        $this->assertTrue($lastInsertedRecord->updated_at != null);
        $this->assertTrue($lastInsertedRecord->created_by == null);
        $this->assertTrue($lastInsertedRecord->updated_by == null);
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

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources_columns' table
        $tableDataCount = DatasourceColumns::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "datasource_id" => ["データソースIDは必ず指定してください。"],
                "datasource_column_number" => ["列番号は必ず指定してください。"],
                "datasource_column_name" => ["Excelヘッダ名は必ず指定してください。"],
                "table_column_id" => ["テーブルカラムIDは必ず指定してください。"],
            ]);
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
            'datasource_id'             => '',
            'datasource_column_number'  => '',
            'datasource_column_name'    => null,
            'table_column_id'           => '',
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources_columns' table
        $tableDataCount = DatasourceColumns::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "datasource_id" => ["データソースIDは必ず指定してください。"],
                "datasource_column_number" => ["列番号は必ず指定してください。"],
                "datasource_column_name" => ["Excelヘッダ名は必ず指定してください。"],
                "table_column_id" => ["テーブルカラムIDは必ず指定してください。"],
            ]);
    }

    /**
     * Validator error - type
     */
    public function testValidatorForType()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'datasource_id'             => 'string',
            'datasource_column_number'  => 'string',
            'datasource_column_name'    => 1,
            'table_column_id'           => 'string',
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources_columns' table
        $tableDataCount = DatasourceColumns::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["データソースIDは整数で指定してください。"])
            ->assertJsonFragment(["列番号は整数で指定してください。"])
            ->assertJsonFragment(["Excelヘッダ名は文字列を指定してください。"])
            ->assertJsonFragment(["テーブルカラムIDは整数で指定してください。"]);
    }

    /**
     * Validator error - max length
     */
    public function testValidatorForMaxLengthError()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            //12 digits
            'datasource_id'             => 123456789012,
            //6 digits
            'datasource_column_number'  => 123456,
            //256 characters
            'datasource_column_name'    => '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456',
            //12 digits
            'table_column_id'           => 123456789012,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources_columns' table
        $tableDataCount = DatasourceColumns::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["データソースIDは1桁から11桁の間で指定してください。"])
            ->assertJsonFragment(["列番号は1桁から5桁の間で指定してください。"])
            ->assertJsonFragment(["Excelヘッダ名は、255文字以下で指定してください。"])
            ->assertJsonFragment(["テーブルカラムIDは1桁から11桁の間で指定してください。"]);
    }

    /**
     * Validator error - max value FOR datasource_column_number
     */
    public function testValidatorForMaxValueForDatasourceColumnNumber()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 16385,
            'datasource_column_name'    => '1',
            'table_column_id'           => 1,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources_columns' table
        $tableDataCount = DatasourceColumns::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["列番号には、16384以下の数字を指定してください。"]);
    }


    /**
     * Validator error - max length NORMAL
     */
    public function testValidatorForMaxLengthSuccess()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        // Add datasource for checking maximum digit
        Datasource::insert([
            [
                'id' => 12345678901,
                'datasource_name' => 'Datasource name 12345678901',
                'table_id' => 1,
                'starting_row_number' => 2,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
            ]
        ]);
        TableColumns::insert([
            [
                'id' => 12345678901,
                'table_id' => 1,
                'column_name' => "Column name 12345678901",
                'column_name_alias' => "Column name alias 12345678901",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
            ]
        ]);

        //Execute -----------------------------
        $postData = [
            //11 digits
            'datasource_id'             => 12345678901,
            //5 digits
            'datasource_column_number'  => 16384,
            //255 characters
            'datasource_column_name'    => '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345',
            //11 digits
            'table_column_id'           => 12345678901,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------

        // Check table data of 'm_datasources_columns' table
        $tableDataCount = DatasourceColumns::count();
        $this->assertEquals(1, $tableDataCount);


        //Check response
        $response
            ->assertStatus(200);
    }

    /**
     * Validator error - minimum number
     */
    public function testValidatorForMinimum()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'datasource_id'             => 0,
            'datasource_column_number'  => 0,
            'datasource_column_name'    => '1',
            'table_column_id'           => 0,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasource_columns' table
        $tableDataCount = DatasourceColumns::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["データソースIDには、1以上の数字を指定してください。"])
            ->assertJsonFragment(["列番号には、1以上の数字を指定してください。"])
            ->assertJsonFragment(["テーブルカラムIDには、1以上の数字を指定してください。"]);
    }

    /**
     * Validator error - if specific datasource is not existed
     */
    public function testValidatorForDatasourceShouldExist()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'datasource_id'             => 100,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'Excelヘッダ名',
            'table_column_id'           => 1,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasource_columns' table
        $tableDataCount = DatasourceColumns::count();
        $this->assertEquals(0, $tableDataCount);

        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "datasource_id" => ["選択されたデータソースIDは正しくありません。"],
            ]);
    }

    /**
     * Validator error - The pair of datasource_id and table_column_id should be unique.
     */
    public function testValidatorForDatasourceIdAndTableColumnIdIsAlreadyExisted()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        //Insert target datasource_column id
        DatasourceColumns::insert([
            [
                'datasource_id'             => 1,
                'datasource_column_number'  => 1,
                'datasource_column_name'    => 'Excelヘッダ名',
                'table_column_id'           => 1,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        ]);

        //Execute -----------------------------
        $postData = [
            'datasource_id'             => 1,   // already existed this pair
            'datasource_column_number'  => 2,
            'datasource_column_name'    => 'Excelヘッダ名 already existed',
            'table_column_id'           => 1,   // already existed this pair
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/datasource-columns', $postData);

        //checking -----------------------------
        $lastInsertedRecord = DatasourceColumns::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasource_columns' (not updated)
        $this->assertNotEquals($postData['datasource_column_number'], $lastInsertedRecord->datasource_column_number);
        $this->assertNotEquals($postData['datasource_column_name'], $lastInsertedRecord->datasource_column_name);

        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["テーブルカラムIDの値は既に存在しています。"]);
    }
}
