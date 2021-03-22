<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Table;
use App\Models\TableColumns;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class DataSourceColumnControllerReadOperationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // m_datasource_columns, m_datasources を空にする
        DatasourceColumns::query()->truncate();
        Datasource::query()->truncate();
        TableColumns::query()->truncate();
        Table::query()->truncate();

        // insert initialized data for m_tables
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

        // insert initialized data for m_table_columns
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
            [
                'id' => 2,
                'table_id' => 1,
                'column_name' => "column_name_2",
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

        //Insert into m_datasources table
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

        //Insert into m_datasource_columns
        DatasourceColumns::insert([
            [
                'id' => 1,
                'datasource_id'             => 1,
                'datasource_column_number'  => 1,
                'datasource_column_name'    => 'datasource column name 1',
                'table_column_id'           => 1,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        ]);
        DatasourceColumns::insert([
            [
                'id' => 2,
                'datasource_id'             => 1,
                'datasource_column_number'  => 2,
                'datasource_column_name'    => 'datasource column name 2',
                'table_column_id'           => 2,
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => '2020-01-02 00:00:00', // Soft deleted
            ]
        ]);
    }

    protected function tearDown(): void
    {
        // m_系テーブルを空にする
        DatasourceColumns::query()->truncate();
        Datasource::query()->truncate();
        TableColumns::query()->truncate();
        Table::query()->truncate();
        parent::tearDown();
    }

    /**
     * Normal test case.
     * Test reading datasource columns on 'm_datasource_columns' table.
     */
    public function testReadDatasourceColumns()
    {
        Log::info(__FUNCTION__);
        $response = $this->get('api/get/datasource-columns');

        $expected_content = [
            [
                'id' => 1,
                'table_column_id'           => 1,
                'datasource_id'             => 1,
                'datasource_column_number'  => 1,
                'datasource_column_name'    => 'datasource column name 1',
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
                'DataSourceName' => 'Datasource name 1',
                'DataSourceTableName' => 'table_name_1',
                'ColumnName' => 'column_name_1',
                "data_source" =>
                [
                    "id" => 1,
                    "datasource_name" => "Datasource name 1",
                    "table_id" => 1,
                    "starting_row_number" => 2,
                    "created_by" => null,
                    "created_at" => "2020-01-01 00:00:00",
                    "updated_by" => null,
                    "updated_at" => "2020-01-01 00:00:00",
                    "deleted_at" => null,
                    "tables" =>
                    [
                        "id" => 1,
                        'table_name' => "table_name_1",
                        'table_name_alias' => "Table Name Alias 1",
                        "created_by" => null,
                        "created_at" => "2020-01-01 00:00:00",
                        "updated_by" => null,
                        "updated_at" => "2020-01-01 00:00:00",
                        'deleted_at' => null,
                    ],
                ],
                "table_definition" =>
                [
                    "id" => 1,
                    "table_id" => 1,
                    "column_name" => "column_name_1",
                    "column_name_alias" => "Column name alias 1",
                    "data_type" => "varchar",
                    "length" => 255,
                    "maximum_number" => null,
                    "decimal_part" => null,
                    "validation" => null,
                    "created_by" => null,
                    "created_at" => "2020-01-01 00:00:00",
                    "updated_by" => null,
                    "updated_at" => "2020-01-01 00:00:00",
                    'deleted_at' => null,
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        // Responseがオブジェクトで返ってきているため配列に変換して確認
        $this->assertEquals($expected_content, $response->original->toArray());
    }
}
