<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileControllerTest extends TestCase
{
    // データベースの初期化にトランザクションを使う
    use RefreshDatabase;

    /**
     * 各テストメソッドの実行前に呼ばれるメソッド
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed");

        // filesを全権削除
        File::query()->delete();

        // filesにデータをセット
        File::create([
            "id" => 11,
            "datasource_id" => 5,
            'original_name' => 'テストファイル１.xlsx',
            'sheet_name' => 'sheet1',
            'updated_at' => '2020-01-01 00:00:00'
        ]);
        File::create([
            "id" => 12,
            "datasource_id" => 4,
            'original_name' => 'テストファイル２.xlsx',
            'sheet_name' => 'sheet2',
            'updated_at' => '2020-01-02 00:00:00'
        ]);
        File::create([
            "id" => 13,
            "datasource_id" => 3,
            'original_name' => 'テストファイル３.xlsx',
            'sheet_name' => 'sheet3',
            'updated_at' => '2020-01-03 00:00:00'
        ]);
        File::create([
            "id" => 14,
            "datasource_id" => 2,
            'original_name' => 'テストファイル４.xlsx',
            'sheet_name' => 'sheet4',
            'updated_at' => '2020-01-04 00:00:00'
        ]);
        File::create([
            "id" => 15,
            "datasource_id" => 1,
            'original_name' => 'テストファイル５.xlsx',
            'sheet_name' => 'sheet5',
            'updated_at' => '2020-01-05 00:00:00'
        ]);
    }

    /**
     * 各テストメソッドの実行後に呼ばれるメソッド
     */
    protected function tearDown(): void
    {
        // filesを全権削除
        File::query()->delete();
        parent::tearDown();
    }

    /**
     * パラメータなしでファイル一覧取得できることを確認する
     * Make sure you can get a list of files without any parameters.
     *
     * @return void
     */
    public function testGetFilesWithoutParameter()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->get('api/v1/excel_files');

        $expected_content = [
            "count" => 5,
            "files" => [
                [
                    "id" => 11,
                    "datasource_id" => 5,
                    'original_name' => 'テストファイル１.xlsx',
                    'sheet_name' => 'sheet1',
                    'updated_at' => '2020-01-01 00:00:00'
                ],
                [
                    "id" => 12,
                    "datasource_id" => 4,
                    'original_name' => 'テストファイル２.xlsx',
                    'sheet_name' => 'sheet2',
                    'updated_at' => '2020-01-02 00:00:00'
                ],
                [
                    "id" => 13,
                    "datasource_id" => 3,
                    'original_name' => 'テストファイル３.xlsx',
                    'sheet_name' => 'sheet3',
                    'updated_at' => '2020-01-03 00:00:00'
                ],
                [
                    "id" => 14,
                    "datasource_id" => 2,
                    'original_name' => 'テストファイル４.xlsx',
                    'sheet_name' => 'sheet4',
                    'updated_at' => '2020-01-04 00:00:00'
                ],
                [
                    "id" => 15,
                    "datasource_id" => 1,
                    'original_name' => 'テストファイル５.xlsx',
                    'sheet_name' => 'sheet5',
                    'updated_at' => '2020-01-05 00:00:00'
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $response->assertExactJson($expected_content);
    }

    /**
     * 論理削除されたデータがファイル一覧に含まれないことを確認する
     * Check that the logically deleted data is not included in the file list
     *
     * @return void
     */
    public function testGetFilesWithoutLogicalDeletedData()
    {
        Log::info(__FUNCTION__);

        // Delete data
        File::where('id', 12)->delete();
        File::where('id', 14)->delete();
        
        // Execute test target
        $response = $this->get('api/v1/excel_files');

        $expected_content = [
            "count" => 3,
            "files" => [
                [
                    "id" => 11,
                    "datasource_id" => 5,
                    'original_name' => 'テストファイル１.xlsx',
                    'sheet_name' => 'sheet1',
                    'updated_at' => '2020-01-01 00:00:00'
                ],
                [
                    "id" => 13,
                    "datasource_id" => 3,
                    'original_name' => 'テストファイル３.xlsx',
                    'sheet_name' => 'sheet3',
                    'updated_at' => '2020-01-03 00:00:00'
                ],
                [
                    "id" => 15,
                    "datasource_id" => 1,
                    'original_name' => 'テストファイル５.xlsx',
                    'sheet_name' => 'sheet5',
                    'updated_at' => '2020-01-05 00:00:00'
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $response->assertExactJson($expected_content);

        // Restore Data
        File::withTrashed()->where('id', 12)->restore();
        File::withTrashed()->where('id', 14)->restore();
    }
    
    /**
     * ソートのカラム名を指定してファイル一覧取得できることを確認する
     * Confirm that the file list can be obtained by specifying the column name of the sort.
     *
     * @return void
     */
    public function testGetFilesWithSortBy()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $column_name = 'datasource_id';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_by=' . $column_name);

        $expected_content = [
            "count" => 5,
            "files" => [
                [
                    "id" => 15,
                    "datasource_id" => 1,
                    'original_name' => 'テストファイル５.xlsx',
                    'sheet_name' => 'sheet5',
                    'updated_at' => '2020-01-05 00:00:00'
                ],
                [
                    "id" => 14,
                    "datasource_id" => 2,
                    'original_name' => 'テストファイル４.xlsx',
                    'sheet_name' => 'sheet4',
                    'updated_at' => '2020-01-04 00:00:00'
                ],
                [
                    "id" => 13,
                    "datasource_id" => 3,
                    'original_name' => 'テストファイル３.xlsx',
                    'sheet_name' => 'sheet3',
                    'updated_at' => '2020-01-03 00:00:00'
                ],
                [
                    "id" => 12,
                    "datasource_id" => 4,
                    'original_name' => 'テストファイル２.xlsx',
                    'sheet_name' => 'sheet2',
                    'updated_at' => '2020-01-02 00:00:00'
                ],
                [
                    "id" => 11,
                    "datasource_id" => 5,
                    'original_name' => 'テストファイル１.xlsx',
                    'sheet_name' => 'sheet1',
                    'updated_at' => '2020-01-01 00:00:00'
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $this->assertEquals($expected_content, $response->original);
    }
    
    /**
     * ソートのカラム名とソート順にascを設定してファイル一覧取得できることを確認する
     * Confirm that the file list can be obtained by setting ASC to the sort column name and sort order.
     *
     * @return void
     */
    public function testGetFilesWithSortByAndAsc()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $column_name = 'id';
        $sort_order = 'asc';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_by=' . $column_name . '&sort_order=' . $sort_order);

        $expected_content = [
            "count" => 5,
            "files" => [
                [
                    "id" => 11,
                    "datasource_id" => 5,
                    'original_name' => 'テストファイル１.xlsx',
                    'sheet_name' => 'sheet1',
                    'updated_at' => '2020-01-01 00:00:00'
                ],
                [
                    "id" => 12,
                    "datasource_id" => 4,
                    'original_name' => 'テストファイル２.xlsx',
                    'sheet_name' => 'sheet2',
                    'updated_at' => '2020-01-02 00:00:00'
                ],
                [
                    "id" => 13,
                    "datasource_id" => 3,
                    'original_name' => 'テストファイル３.xlsx',
                    'sheet_name' => 'sheet3',
                    'updated_at' => '2020-01-03 00:00:00'
                ],
                [
                    "id" => 14,
                    "datasource_id" => 2,
                    'original_name' => 'テストファイル４.xlsx',
                    'sheet_name' => 'sheet4',
                    'updated_at' => '2020-01-04 00:00:00'
                ],
                [
                    "id" => 15,
                    "datasource_id" => 1,
                    'original_name' => 'テストファイル５.xlsx',
                    'sheet_name' => 'sheet5',
                    'updated_at' => '2020-01-05 00:00:00'
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $this->assertEquals($expected_content, $response->original);
    }
    
    /**
     * ソートのカラム名とソート順にdescを設定してファイル一覧取得できることを確認する
     * Confirm that the file list can be obtained by setting desc to the column name and sort order of the sort.
     *
     * @return void
     */
    public function testGetFilesWithSortByAndDesc()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $column_name = 'id';
        $sort_order = 'desc';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_by=' . $column_name . '&sort_order=' . $sort_order);

        $expected_content = [
            "count" => 5,
            "files" => [
                [
                    "id" => 15,
                    "datasource_id" => 1,
                    'original_name' => 'テストファイル５.xlsx',
                    'sheet_name' => 'sheet5',
                    'updated_at' => '2020-01-05 00:00:00'
                ],
                [
                    "id" => 14,
                    "datasource_id" => 2,
                    'original_name' => 'テストファイル４.xlsx',
                    'sheet_name' => 'sheet4',
                    'updated_at' => '2020-01-04 00:00:00'
                ],
                [
                    "id" => 13,
                    "datasource_id" => 3,
                    'original_name' => 'テストファイル３.xlsx',
                    'sheet_name' => 'sheet3',
                    'updated_at' => '2020-01-03 00:00:00'
                ],
                [
                    "id" => 12,
                    "datasource_id" => 4,
                    'original_name' => 'テストファイル２.xlsx',
                    'sheet_name' => 'sheet2',
                    'updated_at' => '2020-01-02 00:00:00'
                ],
                [
                    "id" => 11,
                    "datasource_id" => 5,
                    'original_name' => 'テストファイル１.xlsx',
                    'sheet_name' => 'sheet1',
                    'updated_at' => '2020-01-01 00:00:00'
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $this->assertEquals($expected_content, $response->original);
    }
    
    /**
     * ソートのカラム名とソート順と上限数を設定してファイル一覧取得できることを確認する
     * Confirm that the file list can be obtained by setting the column name,
     * the sort order, and the maximum number of files to be sorted.
     *
     * @return void
     */
    public function testGetFilesWithSortByAndSortOrderAndLimit()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $column_name = 'id';
        $sort_order = 'desc';
        $limit_num = 3;
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_by=' . $column_name . '&sort_order=' . $sort_order . '&limit=' . $limit_num);

        $expected_content = [
            "count" => 3,
            "files" => [
                [
                    "id" => 15,
                    "datasource_id" => 1,
                    'original_name' => 'テストファイル５.xlsx',
                    'sheet_name' => 'sheet5',
                    'updated_at' => '2020-01-05 00:00:00'
                ],
                [
                    "id" => 14,
                    "datasource_id" => 2,
                    'original_name' => 'テストファイル４.xlsx',
                    'sheet_name' => 'sheet4',
                    'updated_at' => '2020-01-04 00:00:00'
                ],
                [
                    "id" => 13,
                    "datasource_id" => 3,
                    'original_name' => 'テストファイル３.xlsx',
                    'sheet_name' => 'sheet3',
                    'updated_at' => '2020-01-03 00:00:00'
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $this->assertEquals($expected_content, $response->original);
    }
    
    /**
     * カラム名に不正な値が設定された場合エラーが返ることを確認する
     * Make sure that an error is returned if the column name is set to an invalid value.
     *
     * @return void
     */
    public function testGetFilesInvalidSortBy()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $sort_by = ';';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_by=' . $sort_by);

        $expected_content = [
            "error_code" => 10,
            "error_message" => "パラメータエラーが発生しました。",
            "error_details_count" => 1,
            "error_details" => [
                "sort byに正しい形式を指定してください。",
            ]
        ];

        // Check result
        $response->assertStatus(400);
        $response->assertExactJson($expected_content);
    }
    
    /**
     * ソート順に不正なデータ（asc/desc以外）が設定された場合エラーが返ることを確認する
     * Confirm that an error will be returned if incorrect data (other than ASC/DESC) is set in the sort order.
     *
     * @return void
     */
    public function testGetFilesInvalidSortOrder()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $column_name = 'id';
        $sort_order = "aasc";
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_by=' . $column_name . '&sort_order=' . $sort_order);

        $expected_content = [
            "error_code" => 10,
            "error_message" => "パラメータエラーが発生しました。",
            "error_details_count" => 1,
            "error_details" => [
                "sort orderに正しい形式を指定してください。",
            ]
        ];

        // Check result
        $response->assertStatus(400);
        $response->assertExactJson($expected_content);
    }
    
    /**
     * 上限数に不正な値（文字列）が設定された場合にエラーが返ることを確認する
     * Make sure that an error is returned when an invalid value (string) is set for the upper limit number.
     *
     * @return void
     */
    public function testGetFilesInvalidLimit()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $limit_num = 'a';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?limit=' . $limit_num);

        $expected_content = [
            "error_code" => 10,
            "error_message" => "パラメータエラーが発生しました。",
            "error_details_count" => 2,
            "error_details" => [
                "limitは整数で指定してください。",
                "limitには、1以上の値を指定してください。",
            ]
        ];

        // Check result
        $response->assertStatus(400);
        $response->assertExactJson($expected_content);
    }
    
    /**
     * 上限数に不正な値（1未満）が設定された場合にエラーが返ることを確認する
     * Confirm that an error is returned if the upper limit is set to an invalid value (less than 1).
     *
     * @return void
     */
    public function testGetFilesInvalidLimit2()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $limit_num = 0;
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?limit=' . $limit_num);

        $expected_content = [
            "error_code" => 10,
            "error_message" => "パラメータエラーが発生しました。",
            "error_details_count" => 1,
            "error_details" => [
                "limitには、1以上の値を指定してください。",
            ]
        ];

        // Check result
        $response->assertStatus(400);
        $response->assertExactJson($expected_content);
    }
    
    /**
     * カラム名の設定なしにソート順を設定した場合エラーが返ることを確認する
     * Confirm that an error is returned when the sort order is set without setting the column name.
     *
     * @return void
     */
    public function testGetFilesWithSortOrderWithoutSortBy()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $sort_order = 'desc';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_order=' . $sort_order);

        $expected_content = [
            "error_code" => 10,
            "error_message" => "パラメータエラーが発生しました。",
            "error_details_count" => 1,
            "error_details" => [
                "sort orderを指定する場合は、sort byも指定してください。",
            ]
        ];

        // Check result
        $response->assertStatus(400);
        $response->assertExactJson($expected_content);
    }
    
    /**
     * 非対応のパラメータが設定された場合エラーが返ることを確認する
     * Make sure that an error is returned when an unsupported parameter is set.
     *
     * @return void
     */
    public function testGetFilesWithUnsupportedParameter()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $invalid_param1 = 'parameter1=1';
        $invalid_param2 = 'parameter2="a"';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?' . $invalid_param1 . "&" . $invalid_param2);

        $expected_content = [
            "error_code" => 20,
            "error_message" => "未対応のパラメータが設定されました。",
            "error_details_count" => 2,
            "error_details" => [
                "parameter1:このパラメータには対応していません。",
                "parameter2:このパラメータには対応していません。",
            ]
        ];

        // Check result
        $response->assertStatus(400);
        $response->assertExactJson($expected_content);
    }
    
    /**
     * 存在しないカラム名を指定した場合エラーが返ることを確認する
     * Make sure that an error is returned when a non-existent column name is specified.
     *
     * @return void
     */
    public function testGetFilesWithInvalidColumn()
    {
        Log::info(__FUNCTION__);
        
        // Generate parameters
        $column_name = 'kotai_no';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_by=' . $column_name);

        $expected_content = [
            "error_code" => 10,
            "error_message" => "予期せぬシステムエラーが発生しました。システム管理者へお問い合わせください。",
            "error_details_count" => 0,
            "error_details" => []
        ];

        // Check result
        $response->assertStatus(500);
        $response->assertExactJson($expected_content);
    }
    
    /**
     * Filesテーブルがない場合エラーが返ることを確認する
     * Make sure you get an error if there is no Files table
     *
     * @return void
     */
    public function testGetFilesWithoutFilesTableOnDB()
    {
        Log::info(__FUNCTION__);

        // Change table name referred by the API
        Schema::rename('files', 'temp_files');

        // Generate parameters
        $column_name = 'kotai_no';
        
        // Execute test target
        $response = $this->get('api/v1/excel_files?sort_by=' . $column_name);

        $expected_content = [
            "error_code" => 10,
            "error_message" => "予期せぬシステムエラーが発生しました。システム管理者へお問い合わせください。",
            "error_details_count" => 0,
            "error_details" => []
        ];

        // Check result
        $response->assertStatus(500);
        $response->assertExactJson($expected_content);
        
        // Returns the table name
        Schema::rename('temp_files', 'files');
    }
}
