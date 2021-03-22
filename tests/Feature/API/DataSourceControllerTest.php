<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\Datasource;
use App\Models\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataSourceControllerTest extends TestCase
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

        // m_datasourcesを全件削除
        Datasource::query()->truncate();

        // m_datasourcesにデータをセット
        Datasource::create([
            "id" => 1,
            "datasource_name" => "データソース１",
            'table_id' => 5,
            'starting_row_number' => 11,
            'created_at' => '2020-01-01 00:00:00',
            'updated_at' => '2020-01-01 00:00:00',
            'deleted_at' => null,
        ]);
        Datasource::create([
            "id" => 2,
            "datasource_name" => "データソース２",
            'table_id' => 4,
            'starting_row_number' => 12,
            'created_at' => '2020-01-02 00:00:00',
            'updated_at' => '2020-01-02 00:00:00',
            'deleted_at' => null,
        ]);
        Datasource::create([
            "id" => 3,
            "datasource_name" => "データソース３",
            'table_id' => 3,
            'starting_row_number' => 13,
            'created_at' => '2020-01-03 00:00:00',
            'updated_at' => '2020-01-03 00:00:00',
            'deleted_at' => null,
        ]);
        Datasource::create([
            "id" => 4,
            "datasource_name" => "データソース４",
            'table_id' => 2,
            'starting_row_number' => 14,
            'created_at' => '2020-01-04 00:00:00',
            'updated_at' => '2020-01-04 00:00:00',
            'deleted_at' => null,
        ]);
        Datasource::create([
            "id" => 5,
            "datasource_name" => "データソース５",
            'table_id' => 1,
            'starting_row_number' => 15,
            'created_at' => '2020-01-05 00:00:00',
            'updated_at' => '2020-01-05 00:00:00',
            'deleted_at' => null,
        ]);
        Datasource::create([
            "id" => 6,
            "datasource_name" => "データソース６",
            'table_id' => 1,
            'starting_row_number' => 16,
            'created_at' => '2020-01-05 00:00:00',
            'updated_at' => '2020-01-05 00:00:00',
            'deleted_at' => '2020-01-06 00:00:00', // Soft deleted
        ]);

        // m_tableを全件削除
        Table::query()->truncate();

        // m_tableにデータをセット
        Table::create([
            "id" => 1,
            "table_name" => "table_name_1",
            "table_name_alias" => "テーブル1",
            'created_at' => '2020-01-05 00:00:00',
            'updated_at' => '2020-01-05 00:00:00',
        ]);
        Table::create([
            "id" => 2,
            "table_name" => "table_name_2",
            "table_name_alias" => "テーブル2",
            'created_at' => '2020-01-05 00:00:00',
            'updated_at' => '2020-01-05 00:00:00',
        ]);
        Table::create([
            "id" => 3,
            "table_name" => "table_name_3",
            "table_name_alias" => "テーブル3",
            'created_at' => '2020-01-05 00:00:00',
            'updated_at' => '2020-01-05 00:00:00',
        ]);
        Table::create([
            "id" => 4,
            "table_name" => "table_name_4",
            "table_name_alias" => "テーブル4",
            'created_at' => '2020-01-05 00:00:00',
            'updated_at' => '2020-01-05 00:00:00',
        ]);
        Table::create([
            "id" => 5,
            "table_name" => "table_name_5",
            "table_name_alias" => "テーブル5",
            'created_at' => '2020-01-05 00:00:00',
            'updated_at' => '2020-01-05 00:00:00',
        ]);
    }

    /**
     * 各テストメソッドの実行後に呼ばれるメソッド
     */
    protected function tearDown(): void
    {
        // filesを全件削除
        Datasource::query()->truncate();
        parent::tearDown();
    }


    /**
     * データソース一覧取得できることを確認する
     * Confirm that the datasource list can be obtained.
     *
     * @return void
     */
    public function testGetDatasources()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->get('api/v1/datasources');

        $expected_content = [
            "count" => 5,
            "datasources" => [
                [
                    "id" => 1,
                    "datasource_name" => "データソース１",
                    'starting_row_number' => 11,
                    "table_id" => 5,
                    'table_name' => 'table_name_5',
                ],
                [
                    "id" => 2,
                    "datasource_name" => "データソース２",
                    'starting_row_number' => 12,
                    "table_id" => 4,
                    'table_name' => 'table_name_4',
                ],
                [
                    "id" => 3,
                    "datasource_name" => "データソース３",
                    'starting_row_number' => 13,
                    "table_id" => 3,
                    'table_name' => 'table_name_3',
                ],
                [
                    "id" => 4,
                    "datasource_name" => "データソース４",
                    'starting_row_number' => 14,
                    "table_id" => 2,
                    'table_name' => 'table_name_2',
                ],
                [
                    "id" => 5,
                    "datasource_name" => "データソース５",
                    'starting_row_number' => 15,
                    "table_id" => 1,
                    'table_name' => 'table_name_1',
                ],
            ]
        ];

        // Check result
        $response->assertStatus(200);
        $this->assertEquals($expected_content, $response->original);
    }

    /**
     * m_datasourcesが空の場合、空配列を取得できることを確認する
     * If m_datasources is empty, make sure you can get an empty array.
     *
     * @return void
     */
    public function testGetDatasourcesWhenMDatasourceTableIsEmpty()
    {
        Log::info(__FUNCTION__);

        // Delete m_datasources
        Datasource::query()->delete();

        // Execute test target
        $response = $this->get('api/v1/datasources');

        $expected_content = [
            "count" => 0,
            "datasources" => []
        ];

        // Check result
        $response->assertStatus(200);
        $this->assertEquals($expected_content, $response->original);
    }

    /**
     * 非対応のパラメータが設定された場合エラーが返ることを確認する
     * Make sure that an error is returned when an unsupported parameter is set.
     *
     * @return void
     */
    public function testGetDatasourcessWithUnsupportParameter()
    {
        Log::info(__FUNCTION__);

        // Generate parameters
        $invalid_param1 = 'parameter1=1';
        $invalid_param2 = 'parameter2="a"';

        // Execute test target
        $response = $this->get('api/v1/datasources?' . $invalid_param1 . "&" . $invalid_param2);

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
        $this->assertEquals($expected_content, $response->original);
    }

    /**
     * m_datasourcesテーブルがない場合エラーが返ることを確認する
     * Make sure you get an error if there is no m_datasources table
     *
     * @return void
     */
    public function testGetFilesWithoutMDatasourcesTableOnDB()
    {
        Log::info(__FUNCTION__);

        // Change table name trefered by the API
        Schema::rename('m_datasources', 'temp_m_datasources');

        // Execute test target
        $response = $this->get('api/v1/datasources');

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
        Schema::rename('temp_m_datasources', 'm_datasources');
    }
}
