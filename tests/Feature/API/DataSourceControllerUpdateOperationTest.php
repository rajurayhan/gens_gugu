<?php

namespace Tests\Feature\API;

use App\Models\Table;
use App\Models\Datasource;
use Tests\TestCase;
use Log;

class DataSourceControllerUpdateOperationTest extends TestCase
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
            [
                'id' => 2,
                'table_name' => "Table Name 2",
                'table_name_alias' => "Table Name Alias 2",
                'updated_by' => 1,
                'updated_at' => '2020-01-02 00:00:00',
            ],
            [
                'id' => 3,
                'table_name' => "Table Name 3",
                'table_name_alias' => "Table Name Alias 3",
                'updated_by' => 1,
                'updated_at' => '2020-01-03 00:00:00',
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
        // truncate all using tables
        Table::query()->truncate();
        DataSource::query()->truncate();

        parent::tearDown();
    }

    /**
     * Normal test case.
     * Test update table on 'm_datasources' table.
     */
    public function testUpdateDatasource()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        // Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
            'datasource_name'       => 'datasource_name_changed',
            'table_id'              => 3,
            'starting_row_number'   => 4,
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data of 'datasource' table
        $this->assertEquals($updatePostData['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($updatePostData['table_id'], $updatedTable->table_id);
        $this->assertEquals($updatePostData['starting_row_number'], $updatedTable->starting_row_number);
        $this->assertTrue($updatedTable->created_at != null);
        $this->assertTrue($updatedTable->updated_at != null);
        $this->assertTrue($updatedTable->created_by == null);
        $this->assertTrue($updatedTable->updated_by == null);

        //Check response
        $updateResponse
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $updatedTable->id,
                'datasource_name' => $updatePostData['datasource_name'],
                'table_id' => $updatePostData['table_id'],
                'starting_row_number' => $updatePostData['starting_row_number'],
            ]);
    }

    /**
     * Validator error - all parameters required - If parameters are not existed
     */
    public function testValidatorForRequired()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        // Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data doesn't update
        $this->assertEquals($postDataAdd['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($postDataAdd['table_id'], $updatedTable->table_id);
        $this->assertEquals($postDataAdd['starting_row_number'], $updatedTable->starting_row_number);


        //Check response
        $updateResponse
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
        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        // Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
            'datasource_name'       => '',
            'table_id'              => null,
            'starting_row_number'   => null,
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data doesn't update
        $this->assertEquals($postDataAdd['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($postDataAdd['table_id'], $updatedTable->table_id);
        $this->assertEquals($postDataAdd['starting_row_number'], $updatedTable->starting_row_number);


        //Check response
        $updateResponse
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
        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        //Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
            'datasource_name'       => 123,
            'table_id'              => 'abc',
            'starting_row_number'   => 'def',
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data doesn't update
        $this->assertEquals($postDataAdd['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($postDataAdd['table_id'], $updatedTable->table_id);
        $this->assertEquals($postDataAdd['starting_row_number'], $updatedTable->starting_row_number);


        //Check response
        $updateResponse
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
        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        //Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
            //256 characters
            'datasource_name'       => '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456',
            //12  characters
            'table_id'              => 123456789012,
            //8 digits
            'starting_row_number'   => 10000000,

        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data doesn't update
        $this->assertEquals($postDataAdd['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($postDataAdd['table_id'], $updatedTable->table_id);
        $this->assertEquals($postDataAdd['starting_row_number'], $updatedTable->starting_row_number);


        //Check response
        $updateResponse
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
        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id'                    => $targetDataSourceId,
            'datasource_name'       => 'normal',
            'table_id'              => 1,
            'starting_row_number'   => static::$MAX_EXCEL_ROW + 1,
        ];

        //TODO need to use "V1" in the url
        $response = $this->post('api/update/data-source', $postData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data doesn't update
        $this->assertEquals($postDataAdd['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($postDataAdd['table_id'], $updatedTable->table_id);
        $this->assertEquals($postDataAdd['starting_row_number'], $updatedTable->starting_row_number);


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

        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        //Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
            //255 characters
            'datasource_name'       => '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345',
            //11  characters
            'table_id'              => 12345678901,
            'starting_row_number'   => static::$MAX_EXCEL_ROW,
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        //Check response
        $updateResponse
            ->assertStatus(200);
    }

    /**
     * Validator error - minimum number
     */
    public function testValidatorForMinimum()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => '1',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        //Execute -----------------------------
        $updatePostData = [
            'datasource_name'       => "normal",
            'table_id'              => 0,
            'starting_row_number'   => 0,
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data doesn't update
        $this->assertEquals($postDataAdd['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($postDataAdd['table_id'], $updatedTable->table_id);
        $this->assertEquals($postDataAdd['starting_row_number'], $updatedTable->starting_row_number);


        //Check response
        $updateResponse
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
        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => '1',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        //Execute -----------------------------
        $updatePostData = [
            'datasource_name'       => '1',
            'table_id'              => 100,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data doesn't update
        $this->assertEquals($postDataAdd['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($postDataAdd['table_id'], $updatedTable->table_id);
        $this->assertEquals($postDataAdd['starting_row_number'], $updatedTable->starting_row_number);


        //Check response
        $updateResponse
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

        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        // Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
            'datasource_name'       => 'Datasource name 1', // same name as existed datasource
            'table_id'              => 3,
            'starting_row_number'   => 4,
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data doesn't update
        $this->assertEquals($postDataAdd['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($postDataAdd['table_id'], $updatedTable->table_id);
        $this->assertEquals($postDataAdd['starting_row_number'], $updatedTable->starting_row_number);


        //Check response
        $updateResponse
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
                'deleted_at' => '2020-01-02 00:00:00', // Soft deleted
            ]
        ]);

        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        // Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
            'datasource_name'       => 'Datasource name 1', // same name as existed but soft deleted datasource
            'table_id'              => 3,
            'starting_row_number'   => 4,
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data of 'datasource' table
        $this->assertEquals($updatePostData['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($updatePostData['table_id'], $updatedTable->table_id);
        $this->assertEquals($updatePostData['starting_row_number'], $updatedTable->starting_row_number);
        $this->assertTrue($updatedTable->created_at != null);
        $this->assertTrue($updatedTable->updated_at != null);
        $this->assertTrue($updatedTable->created_by == null);
        $this->assertTrue($updatedTable->updated_by == null);

        //Check response
        $updateResponse
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $updatedTable->id,
                'datasource_name' => $updatePostData['datasource_name'],
                'table_id' => $updatePostData['table_id'],
                'starting_row_number' => $updatePostData['starting_row_number'],
            ]);
    }

    /**
     * Validator check - save successfully if everything is not changed
     */
    public function testEverythingIsNotChanged()
    {
        Log::info(__FUNCTION__);

        //Preparation -----------------------------

        //Add original data for update
        $postDataAdd = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/data-source', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        // Execute -----------------------------
        $updatePostData = [
            'id'                    => $targetDataSourceId,
            'datasource_name'       => 'datasource_name', // not change
            'table_id'              => 1, // not change
            'starting_row_number'   => 2, // not change
        ];
        //TODO need to use "V1" in the url
        $updateResponse = $this->post('api/update/data-source', $updatePostData);

        //checking -----------------------------
        $updatedTable = Datasource::where('id', $targetDataSourceId)->first();

        // Check table data of 'datasource' table
        $this->assertEquals($updatePostData['datasource_name'], $updatedTable->datasource_name);
        $this->assertEquals($updatePostData['table_id'], $updatedTable->table_id);
        $this->assertEquals($updatePostData['starting_row_number'], $updatedTable->starting_row_number);
        $this->assertTrue($updatedTable->created_at != null);
        $this->assertTrue($updatedTable->updated_at != null);
        $this->assertTrue($updatedTable->created_by == null);
        $this->assertTrue($updatedTable->updated_by == null);

        //Check response
        $updateResponse
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $updatedTable->id,
                'datasource_name' => $updatePostData['datasource_name'],
                'table_id' => $updatePostData['table_id'],
                'starting_row_number' => $updatePostData['starting_row_number'],
            ]);
    }
}
