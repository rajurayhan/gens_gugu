<?php

namespace Tests\Feature\API;

use App\Models\DatasourceColumns;
use App\Models\Table;
use App\Models\TableColumns;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class APITableColumnControllerDeleteOperationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // m_tables を論理削除のデータを含めて全件削除
        Table::query()->truncate();
        // m_table_columns を論理削除のデータを含めて全件削除
        TableColumns::query()->truncate();
        DatasourceColumns::query()->delete();
        parent::tearDown();
    }


    /**
     * Delete Table column Test normal case
     */
    public function testDeleteItemOnMTablesColumns()
    {
        Log::info(__FUNCTION__);

        //Preparation
        //Delete newly created if exists on DB.
        Schema::dropIfExists('table_name');

        //Execute
        $tablePostData = [
            'table_name' => "table_name",
            'table_name_alias' => "table_name_alias",
        ];

        $addTableResponse = $this->post('api/v1/add/tables', $tablePostData);

        $postData = [
            'table_id' => $addTableResponse->original['table']['id'],
            'column_name' => "columnName",
            'column_name_alias' => "columnNameAlias",
            'data_type' => "varchar",
            'length' => 255,
            'maximum_number' => null,
            'decimal_part' => null,
            'validation' => null,
        ];

        $AddResponse = $this->post('api/v1/add/table-columns', $postData);
        $createdColumn = $AddResponse->original['column'];

        //Execute
        $postDataDelete = ['id' => $createdColumn['id']];
        $deleteResponse = $this->post('api/v1/delete/table-columns', $postDataDelete);

        //checking

        //Check record on DB
        $columnCount = TableColumns::where('id', $createdColumn['id'])->count();
        $this->assertEquals(0, $columnCount);
        // check to see if it has been logically deleted
        $this->assertSoftDeleted('m_table_columns', $postData);
        // Check existence on DB
        $this->assertFalse(Schema::hasColumn($tablePostData['table_name'], $postData['column_name']));

        // Check total column number
        $columnsCount = sizeof(Schema::getColumnListing($tablePostData['table_name']));
        $this->assertEquals(4, $columnsCount); // Table Create API add 4 columns.

        // Check Response status
        $deleteResponse->assertStatus(200);
        $this->assertEquals($deleteResponse->original, 'Deleted Successfully!');

        //Clean up
        Schema::dropIfExists($tablePostData['table_name']); // Deleting table technically deletes column.
    }
}
