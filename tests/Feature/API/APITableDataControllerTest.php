<?php

namespace Tests\Feature\API;

use App\Models\Table;
use App\Models\TableColumns;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use DB;

class APITableDataControllerTest extends TestCase
{
    // Initialize database
    use RefreshDatabase;

    protected static $migrated = false;

    /**
     * setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$migrated) {
            self::$migrated = true;

            // Initialize tables (m_table, m_table_columns...)
            $this->artisan("db:seed --class=TranslationSeeder");
            $this->artisan("db:seed --class=TestMasterSeeder");
        }
    }

    /**
     * (private) Setting data for search function
     */
    private function setDataForSearchTestCase(): void
    {
        $targetTableName = 'xls_test_all_types';    // This is created by TestMasterSeeder
        DB::table($targetTableName)->delete();
        DB::table($targetTableName)->insert(
            [
                [
                    'file_id' => 1,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 10,
                    'test_column_date' => '2020/08/01',
                    'test_column_varchar' => 'abc',
                    'test_column_decimal' => '1.1',
                    'test_column_datetime' => '2020/09/01 10:10',
                    'created_at' => '2020/01/01 01:01',
                ],
                [
                    'file_id' => 1,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 20,
                    'test_column_date' => '2020/08/02',
                    'test_column_varchar' => '♂',
                    'test_column_decimal' => '2.2',
                    'test_column_datetime' => '2020/09/02 10:10',
                    'created_at' => '2020/01/01 01:01',
                ],
                [
                    'file_id' => 1,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 30,
                    'test_column_date' => '2020/08/03',
                    'test_column_varchar' => 'ABC',
                    'test_column_decimal' => '3.3',
                    'test_column_datetime' => '2020/09/03 10:10',
                    'created_at' => '2020/01/01 01:01',
                ],
                [
                    'file_id' => 1,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 40,
                    'test_column_date' => '2020/08/04',
                    'test_column_varchar' => 'メス',
                    'test_column_decimal' => '4.4',
                    'test_column_datetime' => '2020/09/04 10:10',
                    'created_at' => '2020/01/01 01:01',
                ],
                [
                    'file_id' => 1,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 50,
                    'test_column_date' => '2020/08/05',
                    'test_column_varchar' => 'ﾒｽ',
                    'test_column_decimal' => '5.5',
                    'test_column_datetime' => '2020/09/05 10:10',
                    'created_at' => '2020/01/01 01:01',
                ],
                [
                    'file_id' => 2,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 60,
                    'test_column_date' => '2020/08/06',
                    'test_column_varchar' => '１．２３',
                    'test_column_decimal' => '6.6',
                    'test_column_datetime' => '2020/09/06 10:10',
                    'created_at' => '2020/02/02 02:02',
                ],
                [
                    'file_id' => 2,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 70,
                    'test_column_date' => '2020/08/07',
                    'test_column_varchar' => '1.23',
                    'test_column_decimal' => '7.7',
                    'test_column_datetime' => '2020/09/07 10:10',
                    'created_at' => '2020/02/02 02:02',
                ],
                [
                    'file_id' => 2,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 80,
                    'test_column_date' => '2020/08/08',
                    'test_column_varchar' => '',
                    'test_column_decimal' => '1.23',
                    'test_column_datetime' => '2020/09/08 10:10',
                    'created_at' => '2020/02/02 02:02',
                ],
                [
                    'file_id' => 3,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 101,
                    'test_column_date' => '2020/01/01',
                    'test_column_varchar' => 'xyz',
                    'test_column_decimal' => '10.1',
                    'test_column_datetime' => '2020/01/01 10:10',
                    'created_at' => '2020/03/03 03:03',
                ],
                [
                    'file_id' => 3,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 102,
                    'test_column_date' => '2020/01/01',
                    'test_column_varchar' => 'xyz',
                    'test_column_decimal' => '10.2',
                    'test_column_datetime' => '2020/01/01 10:10',
                    'created_at' => '2020/03/03 03:03',
                ],
                [
                    'file_id' => 3,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 103,
                    'test_column_date' => '2020/01/01',
                    'test_column_varchar' => 'xyz',
                    'test_column_decimal' => '10.3',
                    'test_column_datetime' => '2020/01/01 10:10',
                    'created_at' => '2020/03/03 03:03',
                ],
                [
                    'file_id' => 3,
                    'file_name' => 'test_data',
                    'test_column_bigint' => 104,
                    'test_column_date' => '2020/01/01',
                    'test_column_varchar' => 'xyz',
                    'test_column_decimal' => '10.4',
                    'test_column_datetime' => '2020/01/01 10:10',
                    'created_at' => '2020/03/03 03:03',
                ],
            ]
        );
    }

    /**
     * Test method: getTableInfo
     * Test point : Success to get m_table data and column information
     */
    public function test_getTableInfo_success()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = [];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('/api/v1/table/' . $tableId, $postData);
        $response->assertOk()
            ->assertJson([
                'id' => $tableId,
                'table_name' => 'xls_test_all_types',
                'table_name_alias' => 'テスト用_全カラムタイプ',
                'columns' => [
                    [
                        'text' => 'アップロード日時',
                        'value' => 'created_at'
                    ],
                    [
                        'text' => 'カラム_BIGINT',
                        'value' => 'test_column_bigint'
                    ],
                    [
                        'text' => 'カラム_DATE',
                        'value' => 'test_column_date'
                    ],
                    [
                        'text' => 'カラム_DATETIME',
                        'value' => 'test_column_datetime'
                    ],
                    [
                        'text' => 'カラム_DECIMAL',
                        'value' => 'test_column_decimal'
                    ],
                    [
                        'text' => 'カラム_文字列',
                        'value' => 'test_column_varchar'
                    ]
                ]
            ]);
    }

    /**
     * Test method: getTableInfo
     * Test point : Error to get m_table data and column information, if table is soft deleted.
     *
     */
    public function test_getTableInfo_failure()
    {
        // Test for ----------------------------
        $tableId = 10;
        $postData = [];

        // preparation -------------------------
        // soft delete xls_test_all_types_with_validation created by TestMasterSeeder
        Table::where('id', $tableId)->delete();

        // Execute & Check -----------------------
        $response = $this->get('/api/v1/table/' . $tableId, $postData);
        $response->assertStatus(404)
            //IF use WebAPIResponse...
            // ->assertJson([
            //     'error_code' => 30,
            //     'error_message' => 'error_message_index.resource_not_found',
            //     'error_details_count' => 0,
            //     'error_details' => []
            // ]);
            ->assertJson(
                [
                    'error' => 'Resource item not found.'
                ]
            );

        // Restore data deleted in preparation
        Table::onlyTrashed()->where('id', $tableId)->restore();
    }

    /**
     * Test method: getTableDetails
     * Test point : Get the target table data successfully
     */
    public function test_getTableData_getAll()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = [];

        // preparation -------------------------
        $table = Table::find($tableId);
        $data = DB::table($table->table_name)->get()->toArray();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId, $postData);
        $response->assertOk()
            ->assertJson($data);
    }

    /**
     * Test method: getTableDetails
     * Test point : Error to get table-details data
     */
    public function test_getTableData_failure()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = [];

        // preparation -------------------------
        // soft delete xls_test_all_types created by TestMasterSeeder
        Table::where('id', $tableId)->delete();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId, $postData);
        $response->assertStatus(404)
            //IF use WebAPIResponse...
            // ->assertJson([
            //     'error_code' => 30,
            //     'error_message' => 'error_message_index.resource_not_found',
            //     'error_details_count' => 0,
            //     'error_details' => []
            // ]);
            ->assertJson(
                [
                    'error' => 'Resource item not found.'
                ]
            );

        // Restore data deleted in preparation
        Table::onlyTrashed()->where('id', $tableId)->restore();
    }

    /**
     * Test method: getTableDetails
     * Test point : Validation for sortBy, sortBy doesn't allow numeric
     */
    public function test_getTableData_validationForPage_doesNotAllowString()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['page' => 'aaa'];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                    'error_code' => 10,
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_details_count' => 1,
                    'error_details'  => ['pageには、数字を指定してください。'],
                ]
            );
    }

    /**
     * Test method: getTableDetails
     * Test point : Validation for itemsPerPage, itemsPerPage doesn't allow numeric
     */
    public function test_getTableData_validationForItemsPerPage_doesNotAllowString()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['itemsPerPage' => 'aaa'];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                    'error_code' => 10,
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_details_count' => 1,
                    'error_details'  => ['items per pageには、数字を指定してください。'],
                ]
            );
    }

    /**
     * Test method: getTableDetails
     * Test point : Validation for sortBy, sortBy doesn't allow string
     */
    public function test_getTableData_validationForSortBy_doesNotAllowString()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['sortBy' => 'aaa'];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                    'error_code' => 10,
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_details_count' => 1,
                    'error_details'  => ['sort byは配列でなくてはなりません。'],
                ]
            );
    }

    /**
     * Test method: getTableDetails
     * Test point : Validation for sortBy, sortBy's record allow everything
     */
    public function test_getTableData_validationForSortBy_insideOfArrayAllowEverything()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['sortBy' => ['test_column_bigint']];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertOk();
    }

    /**
     * Test method: getTableDetails
     * Test point : Validation for sortDesc, sortDesc doesn't allow string
     */
    public function test_getTableData_validationForSortDesc_doesNotAllowString()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['sortDesc' => 'aaa'];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                    'error_code' => 10,
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_details_count' => 1,
                    'error_details'  => ['sort descは配列でなくてはなりません。'],
                ]
            );
    }

    /**
     * Test method: getTableDetails
     * Test point : Validation for sortDesc, sortDesc's record doesn't allow varchar
     */
    public function test_getTableData_validationForSortDesc_insideOfArrayDoesNotAllowVarchar()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['sortDesc' => ['no']];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                    '選択されたsortDesc.0は正しくありません。',
                ]
            );
    }

    /**
     * Test method: getTableDetails
     * Test point : Validation for sortBy, sortDesc's record allow "true"
     */
    public function test_getTableData_validationForSortDesc_insideOfArrayAllowTrueOfString()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['sortDesc' => ['true']];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertOk();
    }

    /**
     * Test method: getTableDetails
     * Test point : Unsupported parameter is not allowed
     */
    public function test_getTableData_UnsupportedParameter()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['unsupported' => 'aaa'];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                    'error_code' => 20,
                    'error_message' => '未対応のパラメータが設定されました。',
                    'error_details_count' => 1,
                    'error_details'  => ['unsupported:このパラメータには対応していません。'],
                ]
            );
    }

    /**
     * Test method: getTableDetails
     * Test point : Empty string is not allowed for search word parameter
     */
    public function test_getTableData_SearchWordsAreEmpty()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['']];

        // preparation -------------------------

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                    'error_code' => 10,
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_details_count' => 1,
                    'error_details'  => ['searchWords.0は文字列を指定してください。'],
                ]
            );
    }

    /**
     * Test method: getTableDetails
     * Test point : Search word parameter works(case insensitive)
     */
    public function test_getTableData_SearchWordsByLowerCase()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['abc']];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 2,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(2, count($responseRecords));
        $this->assertEquals(10, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-01', $responseRecords[0]->test_column_date);
        $this->assertEquals('abc', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(1.1, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-01 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(30, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-08-03', $responseRecords[1]->test_column_date);
        $this->assertEquals('ABC', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(3.3, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-09-03 10:10:00', $responseRecords[1]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Search word parameter works(case insensitive)
     */
    public function test_getTableData_SearchWordsByUpperCase()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['ABC']];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 2,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(2, count($responseRecords));
        $this->assertEquals(10, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-01', $responseRecords[0]->test_column_date);
        $this->assertEquals('abc', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(1.1, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-01 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(30, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-08-03', $responseRecords[1]->test_column_date);
        $this->assertEquals('ABC', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(3.3, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-09-03 10:10:00', $responseRecords[1]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Search word parameter works with Environmental Dependency Text(ex. ♂)
     */
    public function test_getTableData_SearchWordsByEnvironmentalDependencyText()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['♂']];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 1,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(1, count($responseRecords));
        $this->assertEquals(20, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-02', $responseRecords[0]->test_column_date);
        $this->assertEquals('♂', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(2.2, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-02 10:10:00', $responseRecords[0]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Get full-width characters when you search by half-width characters.
     */
    public function test_getTableData_SearchWordsByHalfWidthCharacters()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['ﾒｽ']];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 2,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(2, count($responseRecords));
        $this->assertEquals(40, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-04', $responseRecords[0]->test_column_date);
        $this->assertEquals('メス', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(4.4, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-04 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(50, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-08-05', $responseRecords[1]->test_column_date);
        $this->assertEquals('ﾒｽ', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(5.5, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-09-05 10:10:00', $responseRecords[1]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Get half-width characters when you search by full-width characters.
     */
    public function test_getTableData_SearchWordsByFullWidthCharacters()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['メス']];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 2,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(2, count($responseRecords));
        $this->assertEquals(40, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-04', $responseRecords[0]->test_column_date);
        $this->assertEquals('メス', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(4.4, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-04 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(50, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-08-05', $responseRecords[1]->test_column_date);
        $this->assertEquals('ﾒｽ', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(5.5, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-09-05 10:10:00', $responseRecords[1]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Get full-width digits when you search by half-width digits.
     */
    public function test_getTableData_SearchWordsByHalfWidthDigits()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['1.23']];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 3,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(3, count($responseRecords));
        $this->assertEquals(60, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-06', $responseRecords[0]->test_column_date);
        $this->assertEquals('１．２３', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(6.6, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-06 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(70, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-08-07', $responseRecords[1]->test_column_date);
        $this->assertEquals('1.23', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(7.7, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-09-07 10:10:00', $responseRecords[1]->test_column_datetime);
        $this->assertEquals(80, $responseRecords[2]->test_column_bigint);
        $this->assertEquals('2020-08-08', $responseRecords[2]->test_column_date);
        $this->assertEquals('', $responseRecords[2]->test_column_varchar);
        $this->assertEquals(1.23, $responseRecords[2]->test_column_decimal);
        $this->assertEquals('2020-09-08 10:10:00', $responseRecords[2]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Get half-width digits when you search by full-width digits.
     */
    public function test_getTableData_SearchWordsByFullWidthDigits()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['１．２３']];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 3,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(3, count($responseRecords));
        $this->assertEquals(60, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-06', $responseRecords[0]->test_column_date);
        $this->assertEquals('１．２３', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(6.6, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-06 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(70, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-08-07', $responseRecords[1]->test_column_date);
        $this->assertEquals('1.23', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(7.7, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-09-07 10:10:00', $responseRecords[1]->test_column_datetime);
        $this->assertEquals(80, $responseRecords[2]->test_column_bigint);
        $this->assertEquals('2020-08-08', $responseRecords[2]->test_column_date);
        $this->assertEquals('', $responseRecords[2]->test_column_varchar);
        $this->assertEquals(1.23, $responseRecords[2]->test_column_decimal);
        $this->assertEquals('2020-09-08 10:10:00', $responseRecords[2]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Empty string is not allowed for search word parameter
     */
    public function test_getTableData_SearchWordsByCreatedAt()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = ['searchWords' => ['2020-02-02']];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 3,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(3, count($responseRecords));
        $this->assertEquals(60, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-06', $responseRecords[0]->test_column_date);
        $this->assertEquals('１．２３', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(6.6, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-06 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(70, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-08-07', $responseRecords[1]->test_column_date);
        $this->assertEquals('1.23', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(7.7, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-09-07 10:10:00', $responseRecords[1]->test_column_datetime);
        $this->assertEquals(80, $responseRecords[2]->test_column_bigint);
        $this->assertEquals('2020-08-08', $responseRecords[2]->test_column_date);
        $this->assertEquals('', $responseRecords[2]->test_column_varchar);
        $this->assertEquals(1.23, $responseRecords[2]->test_column_decimal);
        $this->assertEquals('2020-09-08 10:10:00', $responseRecords[2]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Search word parameter works with items per page function
     */
    public function test_getTableData_SearchWordsWithItemsPerPage()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = [
            'page' => 1,
            'itemsPerPage' => 3,
            'searchWords' => ['xyz']
        ];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 4,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(3, count($responseRecords));
        $this->assertEquals(101, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-01-01', $responseRecords[0]->test_column_date);
        $this->assertEquals('xyz', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(10.1, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-01-01 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(102, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-01-01', $responseRecords[1]->test_column_date);
        $this->assertEquals('xyz', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(10.2, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-01-01 10:10:00', $responseRecords[1]->test_column_datetime);
        $this->assertEquals(103, $responseRecords[2]->test_column_bigint);
        $this->assertEquals('2020-01-01', $responseRecords[2]->test_column_date);
        $this->assertEquals('xyz', $responseRecords[2]->test_column_varchar);
        $this->assertEquals(10.3, $responseRecords[2]->test_column_decimal);
        $this->assertEquals('2020-01-01 10:10:00', $responseRecords[2]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Search word parameter works with pagination function
     */
    public function test_getTableData_SearchWordsWithPagination()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = [
            'page' => 2,
            'itemsPerPage' => 3,
            'searchWords' => ['xyz']
        ];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 4,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(1, count($responseRecords));
        $this->assertEquals(104, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-01-01', $responseRecords[0]->test_column_date);
        $this->assertEquals('xyz', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(10.4, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-01-01 10:10:00', $responseRecords[0]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Search word parameter works with sort function
     */
    public function test_getTableData_SearchWordsWithSort()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = [
            'sortBy' => ['test_column_bigint'],
            'sortDesc' => ['true'],
            'searchWords' => ['xyz']
        ];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 4,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(4, count($responseRecords));
        $this->assertEquals(104, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-01-01', $responseRecords[0]->test_column_date);
        $this->assertEquals('xyz', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(10.4, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-01-01 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(103, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-01-01', $responseRecords[1]->test_column_date);
        $this->assertEquals('xyz', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(10.3, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-01-01 10:10:00', $responseRecords[1]->test_column_datetime);
        $this->assertEquals(102, $responseRecords[2]->test_column_bigint);
        $this->assertEquals('2020-01-01', $responseRecords[2]->test_column_date);
        $this->assertEquals('xyz', $responseRecords[2]->test_column_varchar);
        $this->assertEquals(10.2, $responseRecords[2]->test_column_decimal);
        $this->assertEquals('2020-01-01 10:10:00', $responseRecords[2]->test_column_datetime);
        $this->assertEquals(101, $responseRecords[3]->test_column_bigint);
        $this->assertEquals('2020-01-01', $responseRecords[3]->test_column_date);
        $this->assertEquals('xyz', $responseRecords[3]->test_column_varchar);
        $this->assertEquals(10.1, $responseRecords[3]->test_column_decimal);
        $this->assertEquals('2020-01-01 10:10:00', $responseRecords[3]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }

    /**
     * Test method: getTableDetails
     * Test point : Search by multiple words.
     */
    public function test_getTableData_SearchWordsWithMultiWords()
    {
        // Test for ----------------------------
        $tableId = 1;
        $postData = [
            'searchWords' => ['abc', 'メス']
        ];

        // preparation -------------------------
        $this->setDataForSearchTestCase();

        // Execute & Check -----------------------
        $response = $this->get('api/v1/table-data/' . $tableId . '?' . http_build_query($postData));
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'total_count' => 4,
                ]
            );
        $responseRecords = $response->original['records'];
        $this->assertEquals(4, count($responseRecords));
        $this->assertEquals(10, $responseRecords[0]->test_column_bigint);
        $this->assertEquals('2020-08-01', $responseRecords[0]->test_column_date);
        $this->assertEquals('abc', $responseRecords[0]->test_column_varchar);
        $this->assertEquals(1.1, $responseRecords[0]->test_column_decimal);
        $this->assertEquals('2020-09-01 10:10:00', $responseRecords[0]->test_column_datetime);
        $this->assertEquals(30, $responseRecords[1]->test_column_bigint);
        $this->assertEquals('2020-08-03', $responseRecords[1]->test_column_date);
        $this->assertEquals('ABC', $responseRecords[1]->test_column_varchar);
        $this->assertEquals(3.3, $responseRecords[1]->test_column_decimal);
        $this->assertEquals('2020-09-03 10:10:00', $responseRecords[1]->test_column_datetime);
        $this->assertEquals(40, $responseRecords[2]->test_column_bigint);
        $this->assertEquals('2020-08-04', $responseRecords[2]->test_column_date);
        $this->assertEquals('メス', $responseRecords[2]->test_column_varchar);
        $this->assertEquals(4.4, $responseRecords[2]->test_column_decimal);
        $this->assertEquals('2020-09-04 10:10:00', $responseRecords[2]->test_column_datetime);
        $this->assertEquals(50, $responseRecords[3]->test_column_bigint);
        $this->assertEquals('2020-08-05', $responseRecords[3]->test_column_date);
        $this->assertEquals('ﾒｽ', $responseRecords[3]->test_column_varchar);
        $this->assertEquals(5.5, $responseRecords[3]->test_column_decimal);
        $this->assertEquals('2020-09-05 10:10:00', $responseRecords[3]->test_column_datetime);

        // Cleaning up
        DB::table('xls_test_all_types')->delete();
    }
}
