<?php

namespace Tests\Feature\Services;

use App\Models\Table;
use App\Models\TableColumns;
use App\Services\TableDataSearchService;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use DB;
use Tests\TestCase;

class TableDataSearchServiceTest extends TestCase
{
    // Initialize database
    use RefreshDatabase;

    // The flag for DB initialization
    protected static $dbInitialized = false;

    /**
     * setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Only first time when test runs, set up database
        if (static::$dbInitialized == false) {
            // delete all records from m_tables
            Table::query()->truncate();

            // delete all records from m_table_columns
            TableColumns::query()->truncate();

            // drop table for test if exist
            Schema::dropIfExists('test1');

            // insert a record into m_tables
            Table::create([
                "id" => 1,
                "table_name" => "test1",
                'table_name_alias' => "test1_alias",
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
            ]);

            // insert records into m_table_columns
            TableColumns::insert([
                [
                    "id" => 1,
                    "table_id" => 1,
                    "column_name" => "column1",
                    'column_name_alias' => "column1_alias",
                    "data_type" => "bigint",
                    "length" => "10",
                    "maximum_number" => null,
                    "decimal_part" => null,
                    "validation" => null,
                    'created_at' => '2020-01-01 00:00:00',
                    'updated_at' => '2020-01-01 00:00:00',
                ],
                [
                    "id" => 2,
                    "table_id" => 1,
                    "column_name" => "column2",
                    'column_name_alias' => "column2_alias",
                    "data_type" => "date",
                    "length" => null,
                    "maximum_number" => null,
                    "decimal_part" => null,
                    "validation" => null,
                    'created_at' => '2020-01-01 00:00:00',
                    'updated_at' => '2020-01-01 00:00:00',
                ],
                [
                    "id" => 3,
                    "table_id" => 1,
                    "column_name" => "column3",
                    'column_name_alias' => "column3_alias",
                    "data_type" => "datetime",
                    "length" => null,
                    "maximum_number" => null,
                    "decimal_part" => null,
                    "validation" => null,
                    'created_at' => '2020-01-01 00:00:00',
                    'updated_at' => '2020-01-01 00:00:00',
                ],
                [
                    "id" => 4,
                    "table_id" => 1,
                    "column_name" => "column4",
                    'column_name_alias' => "column4_alias",
                    "data_type" => "decimal",
                    "length" => null,
                    "maximum_number" => "10",
                    "decimal_part" => "3",
                    "validation" => null,
                    'created_at' => '2020-01-01 00:00:00',
                    'updated_at' => '2020-01-01 00:00:00',
                ],
                [
                    "id" => 5,
                    "table_id" => 1,
                    "column_name" => "column5",
                    'column_name_alias' => "column5_alias",
                    "data_type" => "varchar",
                    "length" => "255",
                    "maximum_number" => null,
                    "decimal_part" => null,
                    "validation" => null,
                    'created_at' => '2020-01-01 00:00:00',
                    'updated_at' => '2020-01-01 00:00:00',
                ],
                [
                    "id" => 6,
                    "table_id" => 1,
                    "column_name" => "column0",
                    'column_name_alias' => "column0_alias",
                    "data_type" => "bigint",
                    "length" => "10",
                    "maximum_number" => null,
                    "decimal_part" => null,
                    "validation" => null,
                    'created_at' => '2020-01-01 00:00:00',
                    'updated_at' => '2020-01-01 00:00:00',
                ]
            ]);

            // create a new table
            Schema::create('test1', function (Blueprint $table) {
                $table->bigInteger('column1');
                $table->date('column2');
                $table->datetime('column3');
                $table->decimal('column4', 10, 3);
                $table->string('column5', 255);
                $table->bigInteger('column0');
                $table->string('file_name');
                $table->bigInteger('file_id');
                $table->string('created_by')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->string('updated_by')->nullable();
                $table->timestamp('updated_at')->useCurrent();
            });

            // insert records into the new table
            for ($i = 1; $i <= 20; $i++) {
                DB::table('test1')->insert([
                    [
                        'column1' => $i,
                        'column2' => '2020-01-01',
                        'column3' => '2020-01-01 00:00:00',
                        'column4' => $i * 0.01,
                        'column5' => 'character' . (20 - $i),
                        'column0' => 20 - $i,
                        'file_name' => 'datasource1',
                        'file_id' => 1,
                        'created_by' => null,
                        'created_at' => '2020-02-01 00:00:00',
                        'updated_by' => null,
                        'updated_at' => '2020-03-01 00:00:00',
                    ]
                ]);
            }

            // DB initialization if finished
            static::$dbInitialized = true;
        }
    }

    /**
     * Test method: GetTableData
     * Test point : Return "the target table data" by table_id
     * (Return all records of the table)
     */
    public function testGetTableData_GetTableDataByTableId()
    {
        // Execute test target
        $tableId = 1;
        $options = [];
        $tableDataSearchService = new TableDataSearchService();
        $returnValue = $tableDataSearchService->getTableData($tableId, $options);

        $expected_content = [
            'records' => [],
            'total_count' => 20,
        ];

        for ($i = 1; $i <= 20; $i++) {
            $expected_content['records'][] =
                (object) [
                    'column1' => $i,
                    'column2' => '2020-01-01',
                    'column3' => '2020-01-01 00:00:00',
                    'column4' => $i * 0.010,
                    'column5' => 'character' . (20 - $i),
                    'column0' => 20 - $i,
                    'file_name' => 'datasource1',
                    'file_id' => 1,
                    'created_by' => null,
                    'created_at' => '2020-02-01 00:00:00',
                    'updated_by' => null,
                    'updated_at' => '2020-03-01 00:00:00',
                ];
        };

        $this->assertEquals($expected_content, $returnValue);
    }

    /**
     * Test method: getTableData
     * Test point : Return "the target number of table data" if itemsPerPage is set
     */
    public function testGetTableData_ItemsPerPageIs5returns5records()
    {
        // Execute test target
        $tableId = 1;
        $options = ['itemsPerPage' => 5];
        $tableDataSearchService = new TableDataSearchService();
        $returnValue = $tableDataSearchService->getTableData($tableId, $options);

        $expected_content = [
            'records' => [],
            'total_count' => 20,
        ];

        for ($i = 1; $i <= $options['itemsPerPage']; $i++) {
            $expected_content['records'][] =
                (object) [
                    'column1' => $i,
                    'column2' => '2020-01-01',
                    'column3' => '2020-01-01 00:00:00',
                    'column4' => $i * 0.010,
                    'column5' => 'character' . (20 - $i),
                    'column0' => 20 - $i,
                    'file_name' => 'datasource1',
                    'file_id' => 1,
                    'created_by' => null,
                    'created_at' => '2020-02-01 00:00:00',
                    'updated_by' => null,
                    'updated_at' => '2020-03-01 00:00:00',
                ];
        };

        $this->assertEquals($expected_content, $returnValue);
    }

    /**
     * Test method: getTableData
     * Test point : Return "the target page of table data" if page is set
     */
    public function testGetTableData_ItemsPerPageIs5AndPageIs2returnsFrom6thTo10thRecords()
    {
        // Execute test target
        $tableId = 1;
        $options = ['itemsPerPage' => 5, 'page' => 2];
        $tableDataSearchService = new TableDataSearchService();
        $returnValue = $tableDataSearchService->getTableData($tableId, $options);

        $expected_content = [
            'records' => [],
            'total_count' => 20,
        ];

        for ($i = 6; $i <= 10; $i++) {
            $expected_content['records'][] =
                (object) [
                    'column1' => $i,
                    'column2' => '2020-01-01',
                    'column3' => '2020-01-01 00:00:00',
                    'column4' => $i * 0.010,
                    'column5' => 'character' . (20 - $i),
                    'column0' => 20 - $i,
                    'file_name' => 'datasource1',
                    'file_id' => 1,
                    'created_by' => null,
                    'created_at' => '2020-02-01 00:00:00',
                    'updated_by' => null,
                    'updated_at' => '2020-03-01 00:00:00',
                ];
        };

        $this->assertEquals($expected_content, $returnValue);
    }

    /**
     * Test method: getTableData
     * Test point : Return "the target and sorted records of table data" if sortBy is set
     */
    public function testGetTableData_sortByColumn0()
    {
        // Execute test target
        $tableId = 1;
        $options = ['sortBy' => 'column0'];
        $tableDataSearchService = new TableDataSearchService();
        $returnValue = $tableDataSearchService->getTableData($tableId, $options);

        $expected_content = [
            'records' => [],
            'total_count' => 20,
        ];

        for ($i = 20; $i > 0; $i--) {
            $expected_content['records'][] =
                (object) [
                    'column1' => $i,
                    'column2' => '2020-01-01',
                    'column3' => '2020-01-01 00:00:00',
                    'column4' => $i * 0.010,
                    'column5' => 'character' . (20 - $i),
                    'column0' => 20 - $i,
                    'file_name' => 'datasource1',
                    'file_id' => 1,
                    'created_by' => null,
                    'created_at' => '2020-02-01 00:00:00',
                    'updated_by' => null,
                    'updated_at' => '2020-03-01 00:00:00',
                ];
        };

        $this->assertEquals($expected_content, $returnValue);
    }

    /**
     * Test method: getTableData
     * Test point : Return "the target and sorted records of table data" if sortBy is set
     */
    public function testGetTableData_sortDescColumn0()
    {
        // Execute test target
        $tableId = 1;
        $options = ['sortBy' => 'column0', 'sortDesc' => 'true'];
        $tableDataSearchService = new TableDataSearchService();
        $returnValue = $tableDataSearchService->getTableData($tableId, $options);

        $expected_content = [
            'records' => [],
            'total_count' => 20,
        ];

        for ($i = 1; $i <= 20; $i++) {
            $expected_content['records'][] =
                (object) [
                    'column1' => $i,
                    'column2' => '2020-01-01',
                    'column3' => '2020-01-01 00:00:00',
                    'column4' => $i * 0.010,
                    'column5' => 'character' . (20 - $i),
                    'column0' => 20 - $i,
                    'file_name' => 'datasource1',
                    'file_id' => 1,
                    'created_by' => null,
                    'created_at' => '2020-02-01 00:00:00',
                    'updated_by' => null,
                    'updated_at' => '2020-03-01 00:00:00',
                ];
        };

        $this->assertEquals($expected_content, $returnValue);
    }

    /**
     * Test method: getTableData
     * Test point : Return "the target and sorted page records of table data" if All(itemsPerPage/page/sortBy/sortDesc) are set
     */
    public function testGetTableData_setAllParameters()
    {
        // Execute test target
        $tableId = 1;
        $options = ['itemsPerPage' => 5, 'page' => 2, 'sortBy' => 'column0', 'sortDesc' => 'false'];
        $tableDataSearchService = new TableDataSearchService();
        $returnValue = $tableDataSearchService->getTableData($tableId, $options);

        $expected_content = [
            'records' => [],
            'total_count' => 20,
        ];

        for ($i = 15; $i > 10; $i--) {
            $expected_content['records'][] =
                (object) [
                    'column1' => $i,
                    'column2' => '2020-01-01',
                    'column3' => '2020-01-01 00:00:00',
                    'column4' => $i * 0.010,
                    'column5' => 'character' . (20 - $i),
                    'column0' => 20 - $i,
                    'file_name' => 'datasource1',
                    'file_id' => 1,
                    'created_by' => null,
                    'created_at' => '2020-02-01 00:00:00',
                    'updated_by' => null,
                    'updated_at' => '2020-03-01 00:00:00',
                ];
        };

        $this->assertEquals($expected_content, $returnValue);
    }

    /**
     * Test method: getTableHeader
     * Test point : Return "the target table header columns" by table_id
     */
    public function testGetTableHeader_GetTableColumnNameByTableId()
    {
        // Execute test target
        $tableId = 1;
        $tableDataSearchService = new TableDataSearchService();
        $returnValue = $tableDataSearchService->getTableHeader($tableId);

        $expected_content = [
            ['text' => 'アップロード日時', 'value' => 'created_at'],
            ['text' => 'column1_alias', 'value' => 'column1'],
            ['text' => 'column2_alias', 'value' => 'column2'],
            ['text' => 'column3_alias', 'value' => 'column3'],
            ['text' => 'column4_alias', 'value' => 'column4'],
            ['text' => 'column5_alias', 'value' => 'column5'],
            ['text' => 'column0_alias', 'value' => 'column0'],
        ];

        $this->assertEquals($expected_content, $returnValue);
    }
}
