<?php

namespace Tests\Feature\API;

use App\Models\Table;
use App\Models\TableColumns;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Tests\TestCase;
use Log;

class DataSourceColumnControllerDeleteOperationTest extends TestCase
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
        DataSource::query()->truncate();
        DatasourceColumns::query()->truncate();

        // insert initialized data for m_tables / m_table_columns / m_datasource_columns
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
        // truncate all used table records
        Table::query()->truncate();
        TableColumns::query()->truncate();
        DataSource::query()->truncate();
        DatasourceColumns::query()->truncate();

        parent::tearDown();
    }

    /**
     * Normal test case.
     * Test delete table on 'm_datasource_columns' table.
     */
    public function testDeleteDatasourceColumn()
    {

        Log::info(__FUNCTION__);

        //Preparation -----------------------------
        //Add original data for update
        $postDataAdd = [
            'datasource_id'             => 1,
            'datasource_column_number'  => 1,
            'datasource_column_name'    => 'datasource column name',
            'table_column_id'           => 1,
        ];
        //TODO need to use "V1" in the url
        $addResponse = $this->post('api/add/datasource-columns', $postDataAdd);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceColumnId = $addResponseJson->id;

        //Execute -----------------------------
        $postData = [
            'id' => $targetDataSourceColumnId,
        ];
        $deleteResponse = $this->post('api/delete/datasource-columns', $postData);

        //checking -----------------------------
        // Check the record is soft deleted in 'm_datasource_columns' table
        $this->assertSoftDeleted('m_datasource_columns', $postDataAdd);
        // Check existence of record in 'm_datasource_columns' table
        $tableDataCount = DatasourceColumns::where('id', $targetDataSourceColumnId)->count();
        $this->assertEquals(0, $tableDataCount);

        $deleteResponse
            ->assertStatus(200)
            ->assertJsonFragment(['Deleted Successfully!']);
    }
}
