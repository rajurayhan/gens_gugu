<?php

namespace Tests\Feature\API;

use DB;
use App\Models\Table;
use App\Models\TableColumns;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Tests\TestCase;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APITableControllerDeleteOperationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // m_tables を論理削除のデータを含めて全件削除
        Table::query()->truncate();

        // m_tables にデータをセット
        Table::create(
            [
                'id' => 1,
                'table_name' => "Table Name 1",
                'table_name_alias' => "Table Name Alias 1",
                'updated_by' => 1,
                'updated_at' => '2020-01-01 00:00:00',
            ]
        );
        Table::create(
            [
                'id' => 2,
                'table_name' => "Table Name 2",
                'table_name_alias' => "Table Name Alias 2",
                'updated_by' => 1,
                'updated_at' => '2020-01-02 00:00:00',
            ]
        );
        Table::create(
            [
                'id' => 3,
                'table_name' => "Table Name 3",
                'table_name_alias' => "Table Name Alias 3",
                'updated_by' => 1,
                'updated_at' => '2020-01-03 00:00:00',
            ]
        );
        Table::create(
            [
                'id' => 4,
                'table_name' => "Table Name 4",
                'table_name_alias' => "Table Name Alias 4",
                'updated_by' => 1,
                'updated_at' => '2020-01-04 00:00:00',
            ]
        );
        Table::create(
            [
                'id' => 5,
                'table_name' => "Table Name 5",
                'table_name_alias' => "Table Name Alias 5",
                'updated_by' => 1,
                'updated_at' => '2020-01-05 00:00:00',
            ]
        );
    }

    protected function tearDown(): void
    {
        // 論理削除のデータを含めて全件削除
        Table::query()->truncate();
        TableColumns::query()->truncate();
        Datasource::query()->truncate();
        DatasourceColumns::query()->truncate();
        parent::tearDown();
    }

    /**
     * Semi Normal test case.
     * Test confirm message when the raw data table has record.
     */
    public function testConfirmWhenTheRawDataTableHasRecord()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        for ($i = 1; $i < 4; $i++) {
            $postDataOfTableColumn = [
                'table_id' => $createdTableId,
                'column_name' => "columnName" . $i,
                'column_name_alias' => "columnNameAlias",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
            ];
            $this->post('api/v1/add/table-columns', $postDataOfTableColumn);
        }

        DB::table($tableName)->insert(
            ['file_name' => 'testFile', 'file_id' => 1]
        );

        // Execute
        $confirmResponse = $this->get('api/v1/confirm-relation/tables?id=' . $createdTableId);

        // Checking
        $confirmResponse
            ->assertStatus(200)
            ->assertJson([
                'error' => 'This table can not be deleted because the raw data table has data.',
            ]);

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Normal test case.
     * Test confirm message with no conditions.
     */
    public function testConfirmNoConditions()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        // Execute
        $confirmResponse = $this->get('api/v1/confirm-relation/tables?id=' . $createdTableId);

        // Checking
        $confirmResponse
            ->assertStatus(200)
            ->assertJson([
                'message' => '',
            ]);

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Normal test case.
     * Test confirm message when table has 'm_table_columns' record.
     */
    public function testConfirmWhenHavingTableColumn()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        for ($i = 1; $i < 4; $i++) {
            $postDataOfTableColumn = [
                'table_id' => $createdTableId,
                'column_name' => "columnName" . $i,
                'column_name_alias' => "columnNameAlias",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
            ];
            $this->post('api/v1/add/table-columns', $postDataOfTableColumn);
        }

        // Execute
        $confirmResponse = $this->get('api/v1/confirm-relation/tables?id=' . $createdTableId);

        // Checking
        $confirmResponse
            ->assertStatus(200)
            ->assertJson([
                'message' =>  "\"" . $tableName . "\" is related to following definitions."
                    . "\nThese definitions are going to be deleted together."
                    . "\n・table columns"
                    . "\nAre you sure you want to delete these definitions?",
            ]);

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Normal test case.
     * Test confirm message when table has 'm_datasources' record.
     */
    public function testConfirmWhenHavingDatasource()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';
        $datasourceName = 'datasource_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        // add a record in 'm_datasources' table
        $postDataOfDatasource = [
            'datasource_name'       => $datasourceName,
            'table_id'              => $createdTableId,
            'starting_row_number'   => 2,
        ];
        $this->post('api/add/data-source', $postDataOfDatasource);

        // Execute
        $confirmResponse = $this->get('api/v1/confirm-relation/tables?id=' . $createdTableId);

        // Checking
        $confirmResponse
            ->assertStatus(200)
            ->assertJson([
                'message' => "\"" . $tableName . "\" is related to following definitions."
                    . "\nThese definitions are going to be deleted together."
                    . "\n・datasource"
                    . "\n　　・" . $datasourceName
                    . "\nAre you sure you want to delete these definitions?",
            ]);

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Normal test case.
     * Test confirm message when table has 'm_table_columns' and 'm_datasources' and 'm_datasource_columns' record.
     */
    public function testConfirmWhenHavingDatasourceColumn()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';
        $datasourceName = 'datasource_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        $postDataOfTableColumn = [
            'table_id' => $createdTableId,
            'column_name' => "columnName1",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "varchar",
            'length' => 255,
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => null,
        ];
        $addResponse = $this->post('api/v1/add/table-columns', $postDataOfTableColumn);
        $createdTableColumnsId = $addResponse->original['column']['id'];

        // add a record in 'm_datasources' table
        $datasourceNameNoColumns = $datasourceName . '_no_columns';
        $postDataOfDatasource = [
            'datasource_name'       => $datasourceNameNoColumns,
            'table_id'              => $createdTableId,
            'starting_row_number'   => 2,
        ];
        $addResponse = $this->post('api/add/data-source', $postDataOfDatasource);

        $postDataOfDatasource = [
            'datasource_name'       => $datasourceName,
            'table_id'              => $createdTableId,
            'starting_row_number'   => 2,
        ];
        $addResponse = $this->post('api/add/data-source', $postDataOfDatasource);
        $addResponseJson = json_decode($addResponse->content());
        $createdDataSourceId = $addResponseJson->id;

        // add a record in 'm_datasource_columns' table
        $postDataOfDatasourceColumns = [
            'datasource_id'             => $createdDataSourceId,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => $createdTableColumnsId,
        ];
        $this->post('api/add/datasource-columns', $postDataOfDatasourceColumns);

        // Execute
        $confirmResponse = $this->get('api/v1/confirm-relation/tables?id=' . $createdTableId);

        // Checking
        $confirmResponse
            ->assertStatus(200)
            ->assertJson([
                'message' => "\"" . $tableName . "\" is related to following definitions."
                    . "\nThese definitions are going to be deleted together."
                    . "\n・table columns"
                    . "\n・datasource"
                    . "\n　　・" . $datasourceNameNoColumns
                    . "\n　　・" . $datasourceName
                    . "\n・datasource columns"
                    . "\nAre you sure you want to delete these definitions?",
            ]);

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Normal test case.
     * Test delete record on 'm_table' (soft delete) and delete empty raw data table.
     */
    public function testDeleteTable()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        // Execute
        $postData = [
            'id' => $createdTableId,
        ];
        $deleteResponse = $this->post('api/v1/delete/tables', $postData);

        // Checking
        // check existence of row in m-Tables
        $tableCount = Table::where('id', $createdTableId)->count();
        $this->assertEquals(0, $tableCount);
        // check to see if it has been logically deleted
        $this->assertSoftDeleted('m_tables', $postDataOfTable);

        // check table existence on Database
        $this->assertFalse(Schema::hasTable($tableName));

        $deleteResponse
            ->assertStatus(200)
            ->assertJson(
                [
                    'success' => 'Deleted Successfully!',
                ]
            );

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Semi Normal test case.
     * Test delete record on 'm_table' even though raw data table don't exist.
     */
    public function testDeleteTableEvenThoughRawDataTableDontExist()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        Schema::dropIfExists($tableName);

        // Execute
        $postData = [
            'id' => $createdTableId,
        ];
        $deleteResponse = $this->post('api/v1/delete/tables', $postData);

        // Checking
        // check existence of row in m-Tables
        $tableCount = Table::where('id', $createdTableId)->count();
        $this->assertEquals(0, $tableCount);
        // check table existence on Database
        $this->assertFalse(Schema::hasTable($tableName));

        $deleteResponse
            ->assertStatus(200)
            ->assertJson(
                [
                    'success' => 'Deleted Successfully!',
                ]
            );

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Normal test case.
     * Test delete record on 'm_table' and 'm_table_columns' (soft delete) and delete empty raw data table.
     */
    public function testDeleteTableAndTableColumns()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        $postDataOfTableColumn = [];
        for ($i = 1; $i <= 3; $i++) {
            $postDataOfTableColumn[$i] = [
                'table_id' => $createdTableId,
                'column_name' => "columnName" . $i,
                'column_name_alias' => "columnNameAlias",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
            ];
            $this->post('api/v1/add/table-columns', $postDataOfTableColumn[$i]);
        }

        // Execute
        $postData = [
            'id' => $createdTableId,
        ];
        $deleteResponse = $this->post('api/v1/delete/tables', $postData);

        // Checking
        // check existence of row in m-Tables
        $tableCount = Table::where('id', $createdTableId)->count();
        $this->assertEquals(0, $tableCount);
        // check to see if it has been logically deleted in m-Tables
        $this->assertSoftDeleted('m_tables', $postDataOfTable);
        // check table existence on Database
        $this->assertFalse(Schema::hasTable($tableName));
        // check existence of row in m-Table-Columns
        $tableColumnCount = TableColumns::where('table_id', $createdTableId)->count();
        $this->assertEquals(0, $tableColumnCount);
        // check to see if it has been logically deleted in m-Table-Columns
        foreach ($postDataOfTableColumn as $tableColum) {
            $this->assertSoftDeleted('m_table_columns', $tableColum);
        }

        $deleteResponse
            ->assertStatus(200)
            ->assertJson(
                [
                    'success' => 'Deleted Successfully!',
                ]
            );

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Semi Normal test case.
     * Test confirm not to able to delete record on 'm_table' and 'm_table_columns' when the raw data table has record.
     */
    public function testDeleteTableDependedByDatasourceAndTableColumnsWhenTheRawDataTableHasRecord()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        for ($i = 1; $i < 4; $i++) {
            $postDataOfTableColumn = [
                'table_id' => $createdTableId,
                'column_name' => "columnName" . $i,
                'column_name_alias' => "columnNameAlias",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
            ];
            $this->post('api/v1/add/table-columns', $postDataOfTableColumn);
        }

        DB::table($tableName)->insert(
            ['file_name' => 'testFile', 'file_id' => 1]
        );

        // Execute
        $postData = [
            'id' => $createdTableId,
        ];
        $deleteResponse = $this->post('api/v1/delete/tables', $postData);

        // Checking
        // check existence of row in m-Tables
        $tableCount = Table::where('id', $createdTableId)->count();
        $this->assertEquals(1, $tableCount);
        // check table existence on Database
        $this->assertTrue(Schema::hasTable($tableName));
        // check existence of row in m-Table-Columns
        $tableColumnCount = TableColumns::where('table_id', $createdTableId)->count();
        $this->assertEquals(3, $tableColumnCount);

        $deleteResponse
            ->assertStatus(200)
            ->assertJson([
                'error' => 'This table can not be deleted because the raw data table has data.',
            ]);

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Semi Normal test case.
     * Test confirm not to able to delete record on 'm_tables' (soft delete)
     * when the raw data table has record.
     */
    public function testDeleteTableAndTableColumnsWhenTheRawDataTableHasRecord()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        // add a record in 'm_tables' table
        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        // add a record in 'm_datasources' table
        $postDataOfDatasource = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => $createdTableId,
            'starting_row_number'   => 2,
        ];
        $this->post('api/add/data-source', $postDataOfDatasource);

        for ($i = 1; $i < 4; $i++) {
            $postDataOfTableColumn = [
                'table_id' => $createdTableId,
                'column_name' => "columnName" . $i,
                'column_name_alias' => "columnNameAlias",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
            ];
            $this->post('api/v1/add/table-columns', $postDataOfTableColumn);
        }

        DB::table($tableName)->insert(
            ['file_name' => 'testFile', 'file_id' => 1]
        );

        // Execute
        $postData = [
            'id' => $createdTableId,
        ];
        $deleteResponse = $this->post('api/v1/delete/tables', $postData);

        // Checking
        // check existence of row in m-Tables
        $tableCount = Table::where('id', $createdTableId)->count();
        $this->assertEquals(1, $tableCount);
        // check table existence on Database
        $this->assertTrue(Schema::hasTable($tableName));
        // check existence of row in m-Table-Columns
        $tableColumnCount = TableColumns::where('table_id', $createdTableId)->count();
        $this->assertEquals(3, $tableColumnCount);

        $deleteResponse
            ->assertStatus(200)
            ->assertJson(
                [
                    'error' => 'This table can not be deleted because the raw data table has data.',
                ]
            );

        // Clean up
        Schema::dropIfExists($tableName);
    }

    /**
     * Normal test case.
     * Test delete record on 'm_tables', 'm_table_columns' 'm_datasources' and 'm_datasource_columns'. (soft delete)
     */
    public function testDeleteTableAndTableColumnsAndDatasourceAndDatasourceColumns()
    {
        Log::info(__FUNCTION__);

        $tableName = 'table_name';

        // Preparation
        // delete newly created if exists on DB.
        Schema::dropIfExists($tableName);

        // add a record in 'm_tables' table
        $postDataOfTable = [
            'table_name' => $tableName,
            'table_name_alias' => 'table_name_alias',
        ];
        $addResponse = $this->post('api/v1/add/tables', $postDataOfTable);
        $createdTableId = $addResponse->original['table']['id'];

        // add a record in 'm_datasources' table
        $postDataOfDatasource = [
            'datasource_name'       => 'datasource_name1',
            'table_id'              => $createdTableId,
            'starting_row_number'   => 2,
        ];
        $this->post('api/add/data-source', $postDataOfDatasource);

        $postDataOfDatasource = [
            'datasource_name'       => 'datasource_name2',
            'table_id'              => $createdTableId,
            'starting_row_number'   => 2,
        ];
        $addResponse = $this->post('api/add/data-source', $postDataOfDatasource);
        $addResponseJson = json_decode($addResponse->content());
        $createdDataSourceId = $addResponseJson->id;

        // add a record in 'm_table_columns' table
        $createdTableColumnsIds = [];
        for ($i = 1; $i < 4; $i++) {
            $postDataOfTableColumn = [
                'table_id' => $createdTableId,
                'column_name' => "columnName" . $i,
                'column_name_alias' => "columnNameAlias",
                'data_type' => "varchar",
                'length' => 255,
                'maximum_number' => null,
                'decimal_part' => null,
                'validation' => null,
            ];
            $addResponse = $this->post('api/v1/add/table-columns', $postDataOfTableColumn);
            $createdTableColumnsIds[$i] = $addResponse->original['column']['id'];
        }

        // add a record in 'm_datasource_columns' table
        for ($i = 1; $i < 4; $i++) {
            $postDataOfDatasourceColumn = [
                'datasource_id'             => $createdDataSourceId,
                'datasource_column_number'  => 1,
                'datasource_column_name'    => 'datasource column name',
                'table_column_id'           => $createdTableColumnsIds[$i],
            ];
            $this->post('api/add/datasource-columns', $postDataOfDatasourceColumn);
        }

        // Execute
        $postData = [
            'id' => $createdTableId,
        ];
        $deleteResponse = $this->post('api/v1/delete/tables', $postData);

        // Checking
        // check existence of row in m-Tables
        $tableCount = Table::where('id', $createdTableId)->count();
        $this->assertEquals(0, $tableCount);
        // check table existence on Database
        $this->assertFalse(Schema::hasTable($tableName));
        // check existence of row in m-Table-Columns
        $tableColumnCount = TableColumns::where('table_id', $createdTableId)->count();
        $this->assertEquals(0, $tableColumnCount);
        // check existence of row in m-Datasources
        $DatasourceCount = Datasource::where('table_id', $createdTableId)->count();
        $this->assertEquals(0, $DatasourceCount);
        // check existence of row in m-Datasouce-Columns
        $datasourceColumnCount = DatasourceColumns::where('datasource_id', $createdDataSourceId)->count();
        $this->assertEquals(0, $datasourceColumnCount);


        $deleteResponse
            ->assertStatus(200)
            ->assertJson([
                'success' => 'Deleted Successfully!',
            ]);

        // Clean up
        Schema::dropIfExists($tableName);
    }
}
