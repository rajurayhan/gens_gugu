<?php

namespace Tests\Feature\API;

use App\Models\DatasourceColumns;
use App\Models\Table;
use App\Models\TableColumns;
use Tests\TestCase;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APITableColumnControllerReadOperationTest extends TestCase
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
        // m_tables を論理削除のデータを含めて全件削除
        Table::query()->truncate();

        //Create table
        Table::create(
            [
                'id' => 1,
                'table_name' => "Table Name 1",
                'table_name_alias' => "Table Name Alias 1",
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        );

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
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
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
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
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
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
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
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => null,
            ]
        );
        // logically deleted data
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
                'created_by' => null,
                'created_at' => '2020-01-01 00:00:00',
                'updated_by' => null,
                'updated_at' => '2020-01-01 00:00:00',
                'deleted_at' => '2020-02-01 00:00:00',
            ]
        );
    }

    protected function tearDown(): void
    {
        // m_table_columns を論理削除のデータを含めて全件削除
        TableColumns::query()->truncate();
        // m_tables を論理削除のデータを含めて全件削除
        Table::query()->truncate();
        DatasourceColumns::query()->delete();
        parent::tearDown();
    }

    /**
     * Normal test case.
     * Test delete table on 'm_table' table.
     */
    public function testReadTableOnMTablesTable()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->get('api/v1/table-columns');

        $expected_content = [
            "count" => 4,
            "columns" => [
                [
                    'id' => 1,
                    'table_id' => 1,
                    'tableName' => 'Table Name 1',
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
                    'deleted_at' => null,
                ],
                [
                    'id' => 2,
                    'table_id' => 1,
                    'tableName' => 'Table Name 1',
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
                    'deleted_at' => null,
                ],
                [
                    'id' => 3,
                    'table_id' => 1,
                    'tableName' => 'Table Name 1',
                    'column_name' => "Column name 3",
                    'column_name_alias' => "Column name alias 3",
                    'data_type' => "varchar",
                    'length' => 255,
                    'maximum_number' => null,
                    'decimal_part' => null,
                    'validation' => null,
                    'created_by' => null,
                    'created_at' => '2020-01-01 00:00:00',
                    'updated_by' => null,
                    'updated_at' => '2020-01-01 00:00:00',
                    'deleted_at' => null,
                ],
                [
                    'id' => 4,
                    'table_id' => 1,
                    'tableName' => 'Table Name 1',
                    'column_name' => "Column name 4",
                    'column_name_alias' => "Column name alias 4",
                    'data_type' => "varchar",
                    'length' => 255,
                    'maximum_number' => null,
                    'decimal_part' => null,
                    'validation' => null,
                    'created_by' => null,
                    'created_at' => '2020-01-01 00:00:00',
                    'updated_by' => null,
                    'updated_at' => '2020-01-01 00:00:00',
                    'deleted_at' => null,
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $this->assertEquals($expected_content, $response->original);
    }
}
