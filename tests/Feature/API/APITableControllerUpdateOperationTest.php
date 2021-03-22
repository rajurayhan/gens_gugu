<?php

namespace Tests\Feature\API;

use App\Models\Table;
use Tests\TestCase;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APITableControllerUpdateOperationTest extends TestCase
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
        Table::create(
            [
                'id' => 5,
                'table_name' => "Table Name 5",
                'table_name_alias' => "Table Name Alias 5",
                'updated_by' => 1,
                'updated_at' => '2020-01-05 00:00:00',
                'deleted_at' => null,
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
     * Test update table on 'm_table' table.
     */
    public function testUpdateTableOnMTablesTable()
    {
        Log::info(__FUNCTION__);

        //Preparation for test
        $targetTableName = 'table_name_original';
        $targetTableNameAlias = 'table_name_alias_original';
        $newTableName = "table_name_updated";
        $newTableNameAlias = "table_name_alias_updated";
        // Add logically deleted data
        Table::create(
            [
                'id' => 6,
                'table_name' => $newTableName,
                'table_name_alias' => $newTableNameAlias,
                'updated_by' => 1,
                'updated_at' => '2020-01-06 00:00:00',
                'deleted_at' => '2020-02-06 00:00:00',
            ]
        );
        //Delete newly created if exists on DB.
        Schema::dropIfExists($targetTableName);
        Schema::dropIfExists($newTableName);

        $postDataAdd = [
            'table_name' => $targetTableName,
            'table_name_alias' => $targetTableNameAlias,
        ];

        $addResponse = $this->post('api/v1/add/tables', $postDataAdd);

        $createdTable = $addResponse->original['table'];
        $createdRecord = Table::where('id', $createdTable['id'])->first();

        // Execute

        $postDataUpdate = [
            'id'                => $createdTable['id'],
            'table_name'        => $newTableName,
            'table_name_alias'  => $newTableNameAlias,
        ];

        $updateResponse = $this->post('api/v1/update/tables', $postDataUpdate);

        //Check response
        $updateResponse
            ->assertStatus(200)
            ->assertJson(
                [
                    'updated' => true,
                ]
            );
        $this->assertEquals($updateResponse->original['table']['table_name'], $postDataUpdate['table_name']);
        $this->assertEquals($updateResponse->original['table']['table_name_alias'], $postDataUpdate['table_name_alias']);

        // Check
        $updatedRecord = Table::where('id', $createdTable['id'])->first();

        // Check table data of 'm_table'
        $this->assertEquals($postDataUpdate['table_name'], $updatedRecord->table_name);
        $this->assertEquals($postDataUpdate['table_name_alias'], $updatedRecord->table_name_alias);
        $this->assertTrue($updatedRecord->created_at->isSameDay($createdRecord->created_at));
        $this->assertTrue($updatedRecord->updated_at->isSameDay($createdRecord->updated_at));
        $this->assertTrue($updatedRecord->created_by == null);
        $this->assertTrue($updatedRecord->updated_by == null);

        // Check table existence on Database
        $this->assertTrue(Schema::hasTable($newTableName));
        //Check previous table existence
        $this->assertFalse(Schema::hasTable($targetTableName));
        // Check column existence of following table
        $this->assertTrue(
            Schema::hasColumns(
                $newTableName,
                [
                    'file_name',
                    'file_id',
                    'created_by',
                    'created_at',
                ]
            )
        );

        // Check total column number
        $columnsCount = sizeof(Schema::getColumnListing($newTableName));
        $this->assertEquals(4, $columnsCount);

        //Clean up
        Schema::dropIfExists($newTableName);
    }
}
