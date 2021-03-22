<?php

namespace Tests\Feature\API;

use App\Models\Table;
use App\Models\TableColumns;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Log;
use Tests\TestCase;

class DataSourceColumnControllerUpdateOperationTest extends TestCase
{
    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // truncate all used table
        Table::query()->truncate();
        TableColumns::query()->truncate();
        DataSource::query()->truncate();
        DatasourceColumns::query()->truncate();

        // insert initialized data for m_tables / m_table_columns / m_datasources
        Table::insert([
            [
                'id' => 1,
                'table_name' => "Table Name 1",
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
                'column_name' => "Column name 1",
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
            ],
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
     * Update test for normal case
     */
    public function testUpdateNormalCase()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
            'datasource_column_number'  => 2,
            'datasource_column_name'    => 'datasource column name 2',
            'table_column_id'           => 2,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data of 'm_datasource_columns'
        $this->assertEquals($postData['table_column_id'], $updatedRecord->table_column_id);
        // $this->assertEquals($postData['datasource_id'], $updatedRecord->datasource_id);  //datasource_id is not updated
        $this->assertEquals($postData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($postData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertTrue($updatedRecord->created_at != null);
        $this->assertTrue($updatedRecord->updated_at != null);
        $this->assertTrue($updatedRecord->created_by == null);
        $this->assertTrue($updatedRecord->updated_by == null);

        // check response data
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                //inserted data
                'id' => $updatedRecord->id,
                'table_column_id' => $postData['table_column_id'],
                'datasource_id' => $addPostData['datasource_id'],    //datasource_id is not updated
                'datasource_column_number' => $postData['datasource_column_number'],
                'datasource_column_name' => $postData['datasource_column_name'],
            ])
            ->assertJsonFragment([
                //additional data
                'DataSourceName' => 'Datasource name 1',
                'DataSourceTableName' => 'Table Name 1',
                'ColumnName' => 'Column name 2',
            ])
            ->assertJsonFragment([
                //related data
                "table_definition" => [
                    "id" => 2,
                    "table_id" => 1,
                    "column_name" => "Column name 2",
                    "column_name_alias" => "Column name alias 2",
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
                        "table_name" => "Table Name 1",
                        "table_name_alias" => "Table Name Alias 1",
                        "created_at" => "2020-01-01 00:00:00",
                        "created_by" => null,
                        "updated_at" => "2020-01-01 00:00:00",
                        "updated_by" => null,
                        "deleted_at" => null,
                    ],
                ],
            ]);
    }

    /**
     * Validator error - all parameters required - If parameters are not existed
     */
    public function testValidatorForRequired()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data doesn't update
        $this->assertEquals($addPostData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($addPostData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertEquals($addPostData['table_column_id'], $updatedRecord->table_column_id);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
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
        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
            'datasource_column_number'  => null,
            'datasource_column_name'    => '',
            'table_column_id'           => null,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data doesn't update
        $this->assertEquals($addPostData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($addPostData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertEquals($addPostData['table_column_id'], $updatedRecord->table_column_id);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
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
        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
            'datasource_column_number'  => 'string',
            'datasource_column_name'    => 123,
            'table_column_id'           => 'string',
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data doesn't update
        $this->assertEquals($addPostData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($addPostData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertEquals($addPostData['table_column_id'], $updatedRecord->table_column_id);


        //Check response
        $response
            ->assertStatus(422)
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
        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
            //6 digits
            'datasource_column_number'  => 123456,
            //256 characters
            'datasource_column_name'    => '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456',
            //12 digits
            'table_column_id'           => 123456789012,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data doesn't update
        $this->assertEquals($addPostData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($addPostData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertEquals($addPostData['table_column_id'], $updatedRecord->table_column_id);


        //Check response
        $response
            ->assertStatus(422)
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
        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
            'datasource_column_number'  => 16385,
            'datasource_column_name'    => '1',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data doesn't update
        $this->assertEquals($addPostData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($addPostData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertEquals($addPostData['table_column_id'], $updatedRecord->table_column_id);


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

        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
            //5 digits
            'datasource_column_number'  => 16384,
            //255 characters
            'datasource_column_name'    => '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345',
            //11 digits
            'table_column_id'           => 12345678901,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data of 'm_datasources_columns' table
        $this->assertEquals($postData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($postData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertEquals($postData['table_column_id'], $updatedRecord->table_column_id);


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
        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
            'datasource_column_number'  => 0,
            'datasource_column_name'    => '1',
            'table_column_id'           => 0,
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data doesn't update
        $this->assertEquals($addPostData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($addPostData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertEquals($addPostData['table_column_id'], $updatedRecord->table_column_id);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["列番号には、1以上の数字を指定してください。"])
            ->assertJsonFragment(["テーブルカラムIDには、1以上の数字を指定してください。"]);
    }

    /**
     * Validator error - The pair of datasource_id and table_column_id should be unique.
     */
    public function testValidatorForDatasourceIdAndTableColumnIdIsAlreadyExisted()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        DatasourceColumns::insert([
            [
                'datasource_id'             => 1,
                'datasource_column_number'  => 2,
                'datasource_column_name'    => 'datasource column name 2',
                'table_column_id'           => 2,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
            ]
        ]);

        //Add the target record to updated
        $addPostData = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $addPostData);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                        => $targetDataSourceColumnId,
            'datasource_column_number'  => 2,
            'datasource_column_name'    => 'string',
            'table_column_id'           => 2,   // already existed this pair
        ];
        //TODO need to use "V1" in the url
        $response = $this->post('api/update/datasource-columns', $postData);

        //checking -----------------------------
        $updatedRecord = DatasourceColumns::where('id', $targetDataSourceColumnId)->first();

        // Check table data doesn't update
        $this->assertEquals($addPostData['datasource_column_number'], $updatedRecord->datasource_column_number);
        $this->assertEquals($addPostData['datasource_column_name'], $updatedRecord->datasource_column_name);
        $this->assertEquals($addPostData['table_column_id'], $updatedRecord->table_column_id);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(["テーブルカラムIDの値は既に存在しています。"]);
    }
}
