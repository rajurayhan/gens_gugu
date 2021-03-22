<?php

namespace Tests\Feature\API;

use App\Models\Table;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Tests\TestCase;
use Log;

class DataSourceControllerDeleteOperationTest extends TestCase
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
        DataSource::query()->truncate();
        DatasourceColumns::query()->truncate();

        parent::tearDown();
    }

    /**
     * Normal test case.
     * Test delete table on 'm_datasources' table.
     */
    public function testDeleteDatasource()
    {

        Log::info(__FUNCTION__);

        // Preparation -----------------------------
        // add a record in 'm_datasources' table
        $postDataOfDatasource = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        $addResponse = $this->post('api/add/data-source', $postDataOfDatasource);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        // Execute -----------------------------
        $postData = [
            'id' => $targetDataSourceId,
        ];
        $deleteResponse = $this->post('api/delete/data-source', $postData);

        // Checking -----------------------------
        // check the record is soft deleted in 'm_datasources' table
        $this->assertSoftDeleted('m_datasources', $postDataOfDatasource);
        // check existence of record in 'm_datasources' table
        $datasourceCnt = Datasource::where('id', $targetDataSourceId)->count();
        $this->assertEquals(0, $datasourceCnt);

        $deleteResponse
            ->assertStatus(200)
            ->assertJsonFragment(['Deleted Successfully!']);
    }

    /**
     * Normal test case.
     * Test delete table on 'm_datasources' table and 'm_datasource_columns' table.
     */
    public function testDeleteDatasourceAndDatasourceColumns()
    {

        Log::info(__FUNCTION__);

        // Preparation -----------------------------
        // add a record in 'm_datasources' table
        $postDataOfDatasource = [
            'datasource_name'       => 'datasource_name',
            'table_id'              => 1,
            'starting_row_number'   => 2,
        ];
        $addResponse = $this->post('api/add/data-source', $postDataOfDatasource);
        $addResponseJson = json_decode($addResponse->content());
        $targetDataSourceId = $addResponseJson->id;

        // add a record in 'm_datasource_columns' table
        for ($i = 1; $i < 4; $i++) {
            $postDataOfDatasourceColumn = [
                'datasource_id'             => $targetDataSourceId,
                'datasource_column_number'  => $i,
                'datasource_column_name'    => 'datasource column name',
                'table_column_id'           => $i,
            ];
            $this->post('api/add/datasource-columns', $postDataOfDatasourceColumn);
        }

        // Execute -----------------------------
        $postData = [
            'id' => $targetDataSourceId,
        ];
        $deleteResponse = $this->post('api/delete/data-source', $postData);

        // Checking -----------------------------
        // check the record is soft deleted in 'm_datasources' table
        $this->assertSoftDeleted('m_datasources', $postDataOfDatasource);
        // check existence of record in 'm_datasources' table
        $datasourceCnt = Datasource::where('id', $targetDataSourceId)->count();
        $this->assertEquals(0, $datasourceCnt);

        // check check the record is soft deleted in 'm_datasource_columns' table
        $this->assertSoftDeleted('m_datasource_columns', $postDataOfDatasourceColumn);
        // check existence of record in 'm_datasource_columns' table
        $datasourceColumnCnt = DatasourceColumns::where('datasource_id', $targetDataSourceId)->count();
        $this->assertEquals(0, $datasourceColumnCnt);

        $deleteResponse
            ->assertStatus(200)
            ->assertJsonFragment(['Deleted Successfully!']);
    }
}
