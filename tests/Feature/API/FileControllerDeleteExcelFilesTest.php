<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileControllerDeleteExcelFilesTest extends TestCase
{
    // テスト内でDB::transactionのQueryExceptionが発生しなくなるため、use RefreshDatabaseを使わない
    // 代わりに各テストケース内で実施した処理の後処理を行うか、setUpの最初にDBをクリーンアップする必要がある
    // use RefreshDatabase;

    /**
     * 各テストメソッドの実行前に呼ばれるメソッド
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed");

        // filesを全件削除
        File::query()->truncate();

        // filesにデータをセット
        File::create([
            "id" => 1,
            "datasource_id" => 5,
            'original_name' => 'テストファイル１.xlsx',
            'sheet_name' => 'sheet1',
            'table_name' => 'xls_test',
            'updated_at' => '2020-01-01 00:00:00'
        ]);
        File::create([
            "id" => 2,
            "datasource_id" => 4,
            'original_name' => 'テストファイル２.xlsx',
            'sheet_name' => 'sheet2',
            'table_name' => 'xls_test2',
            'updated_at' => '2020-01-02 00:00:00'
        ]);

        // excelデータ格納用テーブル
        Schema::dropIfExists('xls_test');
        Schema::create('xls_test', function (Blueprint $table) {
            $table->string('kotai_no');
            $table->bigInteger('file_id');
            $table->string('file_name');
        });

        // excelデータ格納用テーブルにデータをセット
        DB::table('xls_test')->insert([
            'kotai_no' => '1111111111',
            'file_id' => 1,
            'file_name' => 'test.xls'
        ]);
        DB::table('xls_test')->insert([
            'kotai_no' => '2222222222',
            'file_id' => 1,
            'file_name' => 'test.xls'
        ]);
        DB::table('xls_test')->insert([
            'kotai_no' => '9999999999',
            'file_id' => 2,
            'file_name' => 'test2.xls'
        ]);
    }

    /**
     * 各テストメソッドの実行後に呼ばれるメソッド
     */
    protected function tearDown(): void
    {
        // filesを削除
        File::query()->truncate();
        // excelデータ格納用テーブルを削除
        Schema::drop('xls_test');
        parent::tearDown();
    }

    /**
     * 指定したファイルを削除できることを確認する
     * Verify that the specified file can be deleted.
     *
     * @return void
     */
    public function testDeleteFile()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->delete('api/v1/excel_files/1');

        // Check result
        $response->assertStatus(200);
        $expected_response = [];
        $response->assertExactJson($expected_response);
        $files = File::all();
        $this->assertEquals(1, $files->count());
        $this->assertEquals(2, $files[0]->id);
        $deleted_files = File::onlyTrashed()->get();
        $this->assertEquals(1, $deleted_files->count());
        $this->assertEquals(1, $deleted_files[0]->id);
        $excel_data = DB::table('xls_test')->get();
        $this->assertEquals(1, $excel_data->count());
        $this->assertEquals(2, $excel_data[0]->file_id);
    }

    /**
     * パラメータが設定されていない場合にエラーレスポンスを返すことを確認する
     * Make sure that an error response is returned if no parameters are set.
     *
     * @return void
     */
    public function testDeleteFileWithoutParameter()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->delete('api/v1/excel_files/');

        // Check result
        $response->assertStatus(404);
        $expected_content = [
            "error_code" => 30,
            "error_message" => "指定されたリソースは見つかりませんでした。",
            "error_details_count" => 1,
            "error_details" => [
                "指定されたファイルは存在しません。",
            ]
        ];
        $response->assertExactJson($expected_content);
    }

    /**
     * パラメータが数値でない場合にエラーレスポンスを返すことを確認する
     * Make sure to return an error response if the parameter is not a number.
     *
     * @return void
     */
    public function testDeleteFileWithStringParameter()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->delete('api/v1/excel_files/a');

        // Check result
        $response->assertStatus(404);
        $expected_content = [
            "error_code" => 30,
            "error_message" => "指定されたリソースは見つかりませんでした。",
            "error_details_count" => 1,
            "error_details" => [
                "指定されたファイルは存在しません。",
            ]
        ];
        $response->assertExactJson($expected_content);
    }

    /**
     * パラメータが1以上でない場合にエラーレスポンスを返すことを確認する
     * Make sure to return an error response if the parameter is not more than 1
     *
     * @return void
     */
    public function testDeleteFileWithParameterValue0()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->delete('api/v1/excel_files/0');

        // Check result
        $response->assertStatus(404);
        $expected_content = [
            "error_code" => 30,
            "error_message" => "指定されたリソースは見つかりませんでした。",
            "error_details_count" => 1,
            "error_details" => [
                "指定されたファイルは存在しません。",
            ]
        ];
        $response->assertExactJson($expected_content);
    }

    /**
     * サポートしていないパラメータが渡された場合にエラーレスポンスを返すことを確認する
     * Make sure that an error response is returned when an unsupported parameter is passed
     *
     * @return void
     */
    public function testDeleteFileWithUnsupportedParameter()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->delete('api/v1/excel_files/1?dummy_param=2');

        // Check result
        $response->assertStatus(400);
        $expected_content = [
            "error_code" => 20,
            "error_message" => "未対応のパラメータが設定されました。",
            "error_details_count" => 1,
            "error_details" => [
                "dummy_param:このパラメータには対応していません。",
            ]
        ];
        $response->assertExactJson($expected_content);
    }

    /**
     * 存在しないファイルIDを指定した場合、エラーレスポンスが返ることを確認する
     * Confirm that an error response will be returned when a non-existent file ID is specified.
     *
     * @return void
     */
    public function testDeleteFileWithParameterNotExistOnDB()
    {
        Log::info(__FUNCTION__);

        // Execute test target
        $response = $this->delete('api/v1/excel_files/10');

        // Check result
        $response->assertStatus(404);
        $expected_content = [
            "error_code" => 30,
            "error_message" => "指定されたリソースは見つかりませんでした。",
            "error_details_count" => 1,
            "error_details" => [
                "指定されたファイルは存在しません。",
            ]
        ];
        $response->assertExactJson($expected_content);
    }

    /**
     * 削除すみのファイルIDを指定した場合、エラーレスポンスが返ることを確認する
     * Confirm that an error response is returned when the file ID that is only to be deleted is specified.
     *
     * @return void
     */
    public function testDeleteFileWithDeletedFileIDParameter()
    {
        Log::info(__FUNCTION__);

        // Delete file data
        File::find(1)->delete();

        // Execute test target
        $response = $this->delete('api/v1/excel_files/1');

        // Check result
        $response->assertStatus(404);
        $expected_content = [
            "error_code" => 30,
            "error_message" => "指定されたリソースは見つかりませんでした。",
            "error_details_count" => 1,
            "error_details" => [
                "指定されたファイルは存在しません。",
            ]
        ];
        $response->assertExactJson($expected_content);
    }

    /**
     * excelデータが存在しないファイルIDを指定した場合（テーブルは存在する）、
     * fileテーブルから対象のデータが削除されることを確認する
     * If you specify a file ID that does not have excel data (table exists),
     * make sure that the data is removed from the file table.
     *
     * @return void
     */
    public function testDeleteFileWithParameterNotExistExcelDataOnDB()
    {
        Log::info(__FUNCTION__);

        // Delete Excel data
        DB::table('xls_test')->where('file_id', 1)->delete();

        // Execute test target
        $response = $this->delete('api/v1/excel_files/1');

        // Check result
        $response->assertStatus(200);
        $expected_content = [];
        $response->assertExactJson($expected_content);
        $files = File::all();
        $this->assertEquals(1, $files->count());
        $this->assertEquals(2, $files[0]->id);
        $deleted_files = File::onlyTrashed()->get();
        $this->assertEquals(1, $deleted_files->count());
        $this->assertEquals(1, $deleted_files[0]->id);
        $excel_data = DB::table('xls_test')->get();
        $this->assertEquals(1, $excel_data->count());
        $this->assertEquals(2, $excel_data[0]->file_id);
    }

    /**
     * fileデータに対応するexcel格納用テーブルがない場合、エラーレスポンスが返ることを確認する
     * If the table for storing excel data does not exist, make sure that an error response is returned.
     *
     * @return void
     */
    public function testDeleteFileWithParameterInconsistencyOnDB()
    {
        Log::info(__FUNCTION__);

        // Add inconsistency file data(there is no xls_test3 table on DB)
        File::create([
            "id" => 3,
            "datasource_id" => 3,
            'original_name' => 'テストファイル３.xlsx',
            'sheet_name' => 'sheet3',
            'table_name' => 'xls_test3',
            'updated_at' => '2020-01-03 00:00:00'
        ]);

        // Execute test target
        $response = $this->delete('api/v1/excel_files/3');

        // Check result
        $response->assertStatus(500);
        $expected_content = [
            "error_code" => 10,
            "error_message" => "予期せぬシステムエラーが発生しました。システム管理者へお問い合わせください。",
            "error_details_count" => 0,
            "error_details" => []
        ];
        $response->assertExactJson($expected_content);
        $files = File::orderBy('id')->get();
        $this->assertEquals(3, $files->count());
        $this->assertEquals(1, $files[0]->id);
        $this->assertEquals(2, $files[1]->id);
        $this->assertEquals(3, $files[2]->id);
    }
}
