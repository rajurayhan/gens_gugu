<?php

namespace Tests\Feature\API;

use DB;
use App\Models\Table;
use Tests\TestCase;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APITableControllerAddOperationTest extends TestCase
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
     * Test add new table on 'm_table' table.
     * If a table with the same name exists in m_table, but it has been logically removed, the table can be added.
     */
    public function testAddNewTableOnMTablesTable()
    {
        Log::info(__FUNCTION__);

        //Preparation
        //Delete newly created if exists on DB.
        Schema::dropIfExists('table_name');
        // Add logically deleted data
        Table::create(
            [
                'id' => 6,
                'table_name' => "table_name",
                'table_name_alias' => "table_nameAlias",
                'updated_by' => 1,
                'updated_at' => '2020-01-06 00:00:00',
                'deleted_at' => '2020-02-06 00:00:00',
            ]
        );

        //Execute
        $postData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_nameAlias",
        ];

        $response = $this->post('api/v1/add/tables', $postData);

        //checking
        $lastTable = Table::orderBy('id', 'DESC')->first();

        // Check table data of 'm_table'
        $this->assertEquals('table_name', $lastTable->table_name);
        $this->assertEquals('table_nameAlias', $lastTable->table_name_alias);
        $this->assertTrue($lastTable->created_at != null);
        $this->assertTrue($lastTable->updated_at != null);
        $this->assertTrue($lastTable->created_by == null);
        $this->assertTrue($lastTable->updated_by == null);

        // Check table existence on Database
        $this->assertTrue(Schema::hasTable('table_name'));
        // Check column existence of following table
        $this->assertTrue(
            Schema::hasColumns(
                'table_name',
                [
                    'file_name',
                    'file_id',
                    'created_by',
                    'created_at',
                ]
            )
        );

        // Check total column number
        $columnsCount = sizeof(Schema::getColumnListing('table_name'));
        $this->assertEquals(4, $columnsCount);

        // check nullable
        $con = DB::connection();
        $this->assertEquals(true, $con->getDoctrineColumn('table_name', 'file_name')->getNotnull());
        $this->assertEquals(true, $con->getDoctrineColumn('table_name', 'file_id')->getNotnull());
        $this->assertEquals(false, $con->getDoctrineColumn('table_name', 'created_by')->getNotnull());
        $this->assertEquals(false, $con->getDoctrineColumn('table_name', 'created_at')->getNotnull());

        // check default
        $this->assertEquals(null, $con->getDoctrineColumn('table_name', 'file_name')->getDefault());
        $this->assertEquals(null, $con->getDoctrineColumn('table_name', 'file_id')->getDefault());
        $this->assertEquals(null, $con->getDoctrineColumn('table_name', 'created_by')->getDefault());
        $this->assertEquals(null, $con->getDoctrineColumn('table_name', 'created_at')->getDefault());


        //Check response
        $response
            ->assertStatus(200)
            ->assertJson(
                [
                    'created' => true,
                ]
            );
        $this->assertEquals($response->original['table']['table_name'], $postData['table_name']);
        $this->assertEquals($response->original['table']['table_name_alias'], $postData['table_name_alias']);

        //Clean up
        Schema::dropIfExists('table_name');
        Table::withTrashed()->where('id', 6)->forceDelete();
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
        $response = $this->post('api/v1/add/tables', $postData);

        //checking -----------------------------

        // Check no table data of 'm_table' table
        $tableDataCount = Table::count();
        $this->assertEquals(5, $tableDataCount);


        //Check response
        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                    "table_name" => ["テーブル名は必ず指定してください。"],
                    "table_name_alias" => ["テーブル名（別名）は必ず指定してください。"],
                ]
            );
    }
}
