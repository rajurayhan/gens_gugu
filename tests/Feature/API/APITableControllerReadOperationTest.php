<?php

namespace Tests\Feature\API;

use App\Models\Table;
use Tests\TestCase;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APITableControllerReadOperationTest extends TestCase
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
                'deleted_at' => null,
            ]
        );
        Table::create(
            [
                'id' => 2,
                'table_name' => "Table Name 2",
                'table_name_alias' => "Table Name Alias 2",
                'updated_by' => 1,
                'updated_at' => '2020-01-02 00:00:00',
                'deleted_at' => null,
            ]
        );
        Table::create(
            [
                'id' => 3,
                'table_name' => "Table Name 3",
                'table_name_alias' => "Table Name Alias 3",
                'updated_by' => 1,
                'updated_at' => '2020-01-03 00:00:00',
                'deleted_at' => null,
            ]
        );
        Table::create(
            [
                'id' => 4,
                'table_name' => "Table Name 4",
                'table_name_alias' => "Table Name Alias 4",
                'updated_by' => 1,
                'updated_at' => '2020-01-04 00:00:00',
                'deleted_at' => null,
            ]
        );
        // logically deleted data
        Table::create(
            [
                'id' => 5,
                'table_name' => "Table Name 5",
                'table_name_alias' => "Table Name Alias 5",
                'updated_by' => 1,
                'updated_at' => '2020-01-05 00:00:00',
                'deleted_at' => '2020-01-06 00:00:00',
            ]
        );
    }

    protected function tearDown(): void
    {
        // m_tables を論理削除のデータを含めて全件削除
        Table::query()->truncate();
        parent::tearDown();
    }

    /**
     * Normal test case.
     * Test get tables on 'm_table' table(Excluding logically deleted data).
     */
    public function testReadTableOnMTablesTable()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->get('api/v1/tables');

        // No logically deleted data is included
        $expected_content = [
            "count" => 4,
            "tables" => [
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
                [
                    'id' => 4,
                    'table_name' => "Table Name 4",
                    'table_name_alias' => "Table Name Alias 4",
                    'updated_by' => 1,
                    'updated_at' => '2020-01-04 00:00:00',
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $this->assertEquals($expected_content, $response->original);
    }
}
