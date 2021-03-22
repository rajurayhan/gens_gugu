<?php

namespace Tests\Feature\API;

use App\Models\Table;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Tests\TestCase;
use Log;

class DataSourceControllerAddOperationTest extends TestCase
{

    // Maximum Excel Row Number
    protected static $MAX_EXCEL_ROW = 1048576;

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
        DataSource::query()->truncate();

        // insert initialized data for m_table
        Table::insert([
            [
                'id' => 1,
                'table_name' => "Table Name 1",
                'table_name_alias' => "Table Name Alias 1",
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
            ],
        ]);

        // insert soft deleted data for m_datasources
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
        // truncate all used tables
        Table::query()->truncate();
        DataSource::query()->truncate();

        parent::tearDown();
    }

    /**
     * Normal test case.
     * Test add new table on 'm_datasources' table.
     */
    public function testAddNewDatasource()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------
        $lastTable = Datasource::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasources' table
        $this->assertEquals($postData['datasource_name'], $lastTable->datasource_name);
        $this->assertEquals($postData['table_id'], $lastTable->table_id);
        $this->assertEquals($postData['starting_row_number'], $lastTable->starting_row_number);
        $this->assertTrue($lastTable->created_at != null);
        $this->assertTrue($lastTable->updated_at != null);
        $this->assertTrue($lastTable->created_by == null);
        $this->assertTrue($lastTable->updated_by == null);

        //Check response
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'tableName' => 'Table Name 1'
            ])
            ->assertJsonFragment([
                'id' => $lastTable->id,
                'datasource_name' => $postData['datasource_name'],
                'table_id' => $postData['table_id'],
                'starting_row_number' => $postData['starting_row_number'],
            ]);
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
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources' table
        $tableDataCount = Datasource::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "datasource_name" => ["データソース名は必ず指定してください。"],
                "starting_row_number" => ["開始行は必ず指定してください。"],
                "table_id" => ["テーブルIDは必ず指定してください。"],
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
            'datasource_name'       => null,
            'table_id'              => '',
            'starting_row_number'   => '',
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources' table
        $tableDataCount = Datasource::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                "datasource_name" => ["データソース名は必ず指定してください。"],
                "starting_row_number" => ["開始行は必ず指定してください。"],
                "table_id" => ["テーブルIDは必ず指定してください。"],
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
            'datasource_name'       => 123,
            'table_id'              => 'abc',
            'starting_row_number'   => 'def',
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources' table
        $tableDataCount = Datasource::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["データソース名は文字列を指定してください。"])
            ->assertJsonFragment(["開始行は整数で指定してください。"])
            ->assertJsonFragment(["テーブルIDは整数で指定してください。"]);
    }

    /**
     * Validator error - max length
     */
    public function testValidatorForMaxLength()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            //256 digits
            'datasource_name'       => '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456',
            //12 digits
            'table_id'              => 123456789012,
            //8 digits
            'starting_row_number'   => 10000000,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources' table
        $tableDataCount = Datasource::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["データソース名は、255文字以下で指定してください。"])
            ->assertJsonFragment(["開始行は1桁から7桁の間で指定してください。"])
            ->assertJsonFragment(["テーブルIDは1桁から11桁の間で指定してください。"]);
    }

    /**
     * Validator error - max Value
     */
    public function testValidatorForMaxValue()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'datasource_name'       => 'normal',
            'table_id'              => 1,
            'starting_row_number'   => static::$MAX_EXCEL_ROW + 1,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources' table
        $tableDataCount = Datasource::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["開始行には、" . static::$MAX_EXCEL_ROW . "以下の数字を指定してください。"]);
    }

    /**
     * Validator error - max length NORMAL
     */
    public function testValidatorForMaxLengthSuccess()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        // Add datasource for checking maximum digit
        Table::insert([
            [
                'id' => 12345678901,
                'table_name' => "Table Name 12345678901",
                'table_name_alias' => "Table Name Alias 12345678901",
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
            ],
        ]);

        //Execute -----------------------------
        $postData = [
            //255 digits
            'datasource_name'       => '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345',
            //11  digits
            'table_id'              => 12345678901,
            'starting_row_number'   => static::$MAX_EXCEL_ROW,

        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check table data of 'm_datasources_columns' table
        $tableDataCount = Datasource::count();
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
            'datasource_name'       => "1",
            'table_id'              => 0,
            'starting_row_number'   => 0,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources' table
        $tableDataCount = Datasource::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["開始行には、1以上の数字を指定してください。"])
            ->assertJsonFragment(["テーブルIDには、1以上の数字を指定してください。"]);
    }

    /**
     * Validator error - requested table should be existed
     */
    public function testValidatorForTableShouldBeExisted()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Execute -----------------------------
        $postData = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 100,
            'starting_row_number'   => 2,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check no table data of 'm_datasources' table
        $tableDataCount = Datasource::count();
        $this->assertEquals(0, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["選択されたテーブルIDは正しくありません。"]);
    }

    /**
     * Validator error - datasource name should be unique
     */
    public function testValidatorForDatasourceNameShouldBeUnique()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        // insert to m_datasources
        Datasource::insert([
            [
                'id' => 2,
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

        //Execute -----------------------------
        $postData = [
            'datasource_name'       => 'Datasource name 1', // same name as existed datasource
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------

        // Check no added table data of 'm_datasources' table
        $tableDataCount = Datasource::count();
        $this->assertEquals(1, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["データソース名の値は既に存在しています。"]);
    }

    /**
     * Validator check - datasource name should be unique but except soft delete record
     */
    public function testValidatorForDatasourceNameShouldBeUniqueButExceptSoftDeleteRecord()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        // insert soft deleted data to m_datasources
        Datasource::insert([
            [
                'id' => 2,
                'datasource_name' => 'Datasource name 1',
                'table_id' => 1,
                'starting_row_number' => 2,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => '2020-01-02 00:00:00', // Soft deleted
            ]
        ]);

        //Execute -----------------------------
        $postData = [
            'datasource_name'       => 'Datasource name 1', // same name as existed but soft deleted datasource
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/add/data-source', $postData);

        //checking -----------------------------
        $lastTable = Datasource::orderBy('id', 'DESC')->first();

        // Check table data of 'm_datasources' table
        $this->assertEquals($postData['datasource_name'], $lastTable->datasource_name);
        $this->assertEquals($postData['table_id'], $lastTable->table_id);
        $this->assertEquals($postData['starting_row_number'], $lastTable->starting_row_number);
        $this->assertTrue($lastTable->created_at != null);
        $this->assertTrue($lastTable->updated_at != null);
        $this->assertTrue($lastTable->created_by == null);
        $this->assertTrue($lastTable->updated_by == null);

        //Check response
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'tableName' => 'Table Name 1'
            ])
            ->assertJsonFragment([
                'id' => $lastTable->id,
                'datasource_name' => $postData['datasource_name'],
                'table_id' => $postData['table_id'],
                'starting_row_number' => $postData['starting_row_number'],
            ]);
    }
}
