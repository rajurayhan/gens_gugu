<?php

namespace Tests\Feature;

use DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadedFileControllerForExcelTest extends TestCase
{
    /**
     * Setup
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed --class=TestMasterSeeder");
    }

    /**
     * tearDown
     */
    public function tearDown(): void
    {
        DB::table('xls_test_all_types')->truncate();
        DB::table('xls_test_all_types_with_validation')->truncate();
        parent::tearDown();
    }


    /**
     * (private) Call API
     *
     * @param  params
     * @return Response
     */
    private function callAPI($params)
    {
        extract($params);
        $mimeType = null;
        $error = null;
        $test = true;

        // Executing
        $response = $this->post(
            '/upload-excel',
            [
                'file' => new UploadedFile($path, $originalName, $mimeType, $error, $test),
                'datasource_id' => $datasourceId,
                'sheet_name' => $sheetName,
                'start_row' => $startRow,
                'end_row' => $endRow,
                'mode' => $mode
            ]
        );

        return $response;
    }


    /**
     * Uploadable Excel file by append mode
     * 通常のExcelがアップロードできる
     */
    public function test_AbleToUploadExcel_AppendMode()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types.xlsx');
        $originalName = 'xls_all_types.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 10,
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xlsx',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->file_name, $originalName);
        // $this->assertEquals($lastInsertedRecords[0]->file_id, ?);
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文字列');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');
        $this->assertTrue($lastInsertedRecords[0]->created_at != null);
        $this->assertTrue($lastInsertedRecords[0]->updated_at != null);
        $this->assertTrue($lastInsertedRecords[0]->created_by == null);
        $this->assertTrue($lastInsertedRecords[0]->updated_by == null);

        // Delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }

    /**
     * Uploadable Excel file by replace mode
     * 通常のExcelがアップロードできる
     */
    public function test_AbleToUploadExcel_ReplaceMode_InTheCaseOfSameFilesDoNotExistOnDB()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types.xlsx');
        $originalName = 'xls_all_types.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'replace';

        // Prepare for this test case. delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 10,
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '洗い替え'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xlsx',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->file_name, $originalName);
        // $this->assertEquals($lastInsertedRecords[0]->file_id, ?);
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文字列');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');
        $this->assertTrue($lastInsertedRecords[0]->created_at != null);
        $this->assertTrue($lastInsertedRecords[0]->updated_at != null);
        $this->assertTrue($lastInsertedRecords[0]->created_by == null);
        $this->assertTrue($lastInsertedRecords[0]->updated_by == null);

        // Prepare for this test case. delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }

    /**
     * Normal Excel can be uploaded.
     * Data with the same file name, same sheet name and same data source ID are deleted before registration.
     * 通常のExcelがアップロードできる。同じデータソースID同じファイル名同じシート名のデータは削除してから登録する
     */
    public function test_AbleToUploadExcel_ReplaceMode_InTheCaseOfSameFileSameSheetExistsOnDB()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types.xlsx');
        $originalName = 'xls_all_types_unique.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'replace';

        // Prepare for this test case. delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 10,
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '洗い替え'
                ]
            );

        // first uploaded data is deleted form files table
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->orderBy('created_at', 'asc')
            ->get();
        $this->assertNotNull($files[0]->deleted_at);
        // first uploaded data is deleted form row table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();
        foreach ($lastInsertedRecords as $record) {
            $this->assertNotEquals($record->file_id, $files[0]->id);
        }

        // second uploaded data is inserted to files table
        $this->assertNull($files[1]->deleted_at);
        // second uploaded data is inserted to row table
        foreach ($lastInsertedRecords as $record) {
            $this->assertEquals($record->file_id, $files[1]->id);
        }

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->file_name, $originalName);
        // $this->assertEquals($lastInsertedRecords[0]->file_id, ?);
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文字列');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');
        $this->assertTrue($lastInsertedRecords[0]->created_at != null);
        $this->assertTrue($lastInsertedRecords[0]->updated_at != null);
        $this->assertTrue($lastInsertedRecords[0]->created_by == null);
        $this->assertTrue($lastInsertedRecords[0]->updated_by == null);

        // delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }

    /**
     * Normal Excel can be uploaded.
     * Data with the same file name and same data source ID should be deleted before registration.
     * 通常のExcelがアップロードできる。同じデータソースID同じファイル名異なるシート名のデータは削除せずに登録する
     */
    public function test_AbleToUploadExcel_ReplaceMode_InTheCaseOfSameFileDifferentSheetExistsOnDB()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types.xlsx');
        $originalName = 'xls_all_types_unique.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'replace';

        // delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Executing
        $sheetNameOld = $sheetName;
        $sheetName = 'normal2';
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 10,
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '洗い替え'
                ]
            );

        // first uploaded data is not deleted form files table
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->where('sheet_name', $sheetNameOld)
            ->orderBy('created_at', 'asc')
            ->get();
        $this->assertNull($files[0]->deleted_at);
        // first uploaded data is not deleted form row table
        $firstInsertedRecords = DB::table('xls_test_all_types')->where('file_id', $files[0]->id)->get();
        $this->assertEquals(1, count($firstInsertedRecords));

        // second uploaded data is inserted to files table
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->where('sheet_name', $sheetName)
            ->orderBy('created_at', 'asc')
            ->get();
        $this->assertNull($files[0]->deleted_at);
        // second uploaded data is inserted to row table
        $secondInsertedRecords = DB::table('xls_test_all_types')->where('file_id', $files[0]->id)->get();
        $this->assertEquals(1, count($secondInsertedRecords));

        $insertedRecords = DB::table('xls_test_all_types')->get();
        // Check number of 'xls_test_all_types' records
        $this->assertEquals(2, count($insertedRecords));

        // Check table data of 'xls_test_all_types'
        for ($i = 0; $i < $insertedRecords->count(); $i++) {
            $this->assertEquals($insertedRecords[$i]->file_name, $originalName);
            // $this->assertEquals($insertedRecords[$i]->file_id, ?);
            $this->assertEquals($insertedRecords[$i]->test_column_bigint, 12345);
            $this->assertEquals($insertedRecords[$i]->test_column_date, '2020-07-29');
            $this->assertEquals($insertedRecords[$i]->test_column_varchar, '文字列');
            $this->assertEquals($insertedRecords[$i]->test_column_decimal, 123.45);
            $this->assertEquals($insertedRecords[$i]->test_column_datetime, '2020-07-29 17:00:00');
            $this->assertTrue($insertedRecords[$i]->created_at != null);
            $this->assertTrue($insertedRecords[$i]->updated_at != null);
            $this->assertTrue($insertedRecords[$i]->created_by == null);
            $this->assertTrue($insertedRecords[$i]->updated_by == null);
        }

        // delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }

    /**
     * When there are two or more data with the same data source ID and the same file name and the same sheet name,
     * the message is returned without replacing the data.
     * 同じデータソースID同じファイル名同じシート名のデータが複数存在する場合、データの洗い替えを行わずメッセージを返す
     */
    public function test_ReplaceMode_InTheCaseOfSameFileSameSheetExistTwoOnDB()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types.xlsx');
        $originalName = 'xls_all_types_unique.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        //
        $filesBeforeReplace = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->orderBy('created_at', 'asc')
            ->get();

        // Executing
        $mode = 'replace';
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 20,
                    'message' => 'ファイル名とシート名が同じデータがすでに複数アップロードされているため、洗い替えできません。すでにアップロードされているデータを確認するか、ファイルを見直してください。',
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '洗い替え'
                ]
            );

        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->orderBy('created_at', 'asc')
            ->get();

        // 最初のアップロードファイルのデータが削除されていないこと
        $this->assertNull($files[0]->deleted_at);
        $firstInsertedRecords = DB::table('xls_test_all_types')->where('file_id', $files[0]->id)->get();
        $this->assertEquals(1, count($firstInsertedRecords));

        // 2つめのアップロードファイルのデータが削除されていないこと
        $this->assertNull($files[1]->deleted_at);
        $firstInsertedRecords = DB::table('xls_test_all_types')->where('file_id', $files[1]->id)->get();
        $this->assertEquals(1, count($firstInsertedRecords));

        // 3つめのアップロードファイルのデータが登録されていないこと
        $this->assertEquals($filesBeforeReplace, $files);

        // delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }

    /**
     * To roll back deleted data when a validation error occurs when registering data in the data-review mode.
     * データの洗い替えモードでデータ登録する際にバリデーションエラーが発生した場合、削除したデータをロールバックすること
     */
    public function test_ReplaceMode_InTheCaseOfSameFileSameSheetExistButValidationErrorOccur()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types.xlsx');
        $originalName = 'xls_all_types_unique.xlsx';

        // Set parameters for posting
        $datasourceId = 10;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'replace';

        // Prepare for this test case. delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        $filesBeforeReplace = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->orderBy('created_at', 'asc')
            ->get();

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_validation_error.xlsx');
        $originalName = 'xls_all_types_unique.xlsx';
        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                    'error_code' => 20,
                    "error_message" => '不正なデータがあります。',
                    "error_count" => 1,
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '洗い替え'
                ]
            );

        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->orderBy('created_at', 'asc')
            ->get();

        // 最初のアップロードファイルのデータが削除されていないこと
        $this->assertNull($files[0]->deleted_at);
        $firstInsertedRecords = DB::table('xls_test_all_types_with_validation')->where('file_id', $files[0]->id)->get();
        $this->assertEquals(1, count($firstInsertedRecords));

        // 2つめのアップロードファイルのデータが登録されていないこと
        $this->assertEquals($filesBeforeReplace, $files);

        // delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }

    /**
     * Uploadable Excel file, extension is xls (old excel file)
     * 通常のExcelがアップロードできる - 拡張子「xls」（旧Excelの拡張子）
     */
    public function test_AbleToUploadExcel_extensionXls()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_extension_xls.xls');
        $originalName = 'xls_all_types_extension_xls.xls';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 10,
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xls',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->file_name, $originalName);
        // $this->assertEquals($lastInsertedRecords[0]->file_id, ?);
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文字列');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');
        $this->assertTrue($lastInsertedRecords[0]->created_at != null);
        $this->assertTrue($lastInsertedRecords[0]->updated_at != null);
        $this->assertTrue($lastInsertedRecords[0]->created_by == null);
        $this->assertTrue($lastInsertedRecords[0]->updated_by == null);

        // Delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }


    /**
     * Uploadable Excel file, extension is xlsm (Excel with macro)
     * 通常のExcelがアップロードできる - 拡張子「xlsm」（マクロ有効Excel）
     */
    public function test_AbleToUploadExcel_extensionXlsm()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_extension_xlsm.xlsm');
        $originalName = 'xls_all_types_extension_xlsm.xlsm';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 10,
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xlsm',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->file_name, $originalName);
        // $this->assertEquals($lastInsertedRecords[0]->file_id, ?);
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文字列');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');
        $this->assertTrue($lastInsertedRecords[0]->created_at != null);
        $this->assertTrue($lastInsertedRecords[0]->updated_at != null);
        $this->assertTrue($lastInsertedRecords[0]->created_by == null);
        $this->assertTrue($lastInsertedRecords[0]->updated_by == null);

        // Delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }


    /**
     * Uploadable Excel file, only 1 column is set value
     * 1カラムだけ値が入っているExcelがアップロードできる
     */
    public function test_AbleToUploadExcel_only1ColumnIsSetValue()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_only_1_column_is_set.xlsx');
        $originalName = 'xls_all_types_only_1_column_is_set.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xlsx',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->file_name, $originalName);
        // $this->assertEquals($lastInsertedRecords[0]->file_id, ?);
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, null);
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, null);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, null);
        $this->assertTrue($lastInsertedRecords[0]->created_at != null);
        $this->assertTrue($lastInsertedRecords[0]->updated_at != null);
        $this->assertTrue($lastInsertedRecords[0]->created_by == null);
        $this->assertTrue($lastInsertedRecords[0]->updated_by == null);
    }

    /**
     * Don't import records which all columns are only formula or data only in the out of scope
     * その行が数式のみのカラムのみ、取り込み対象外のカラムにデータが入っている場合、その行を取り込まない
     *
     * ※ configに設定がない場合、trimがfullになっていることも同時に確認
     */
    public function test_doNotImportRecordsWhichAllColumnsAreOnlyFormulaOrOnlySpacesOrDataOnlyInTheOutOfScope()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_all_formula_and_out_of_scope.xlsx');
        $originalName = 'xls_all_types_all_formula_and_out_of_scope.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xlsx',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文　字 列');    //※文字列内の全半角スペースは除去されない
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');
    }

    /**
     * Trimming if config is full
     * 設定が full の場合全て（全角スペースも） trim する
     */
    public function test_trimmingIfConfigIsFull()
    {
        Storage::fake('public');

        //change config (envを変更できないため、設定ファイルを直接変更する)
        config(['excel.trim' => 'full']);

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_all_formula_and_out_of_scope.xlsx');
        $originalName = 'xls_all_types_all_formula_and_out_of_scope.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xlsx',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文　字 列');    //※文字列内の全半角スペースは除去されない
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');
    }

    /**
     * Trimming if config is normal
     * 設定が normal の場合通常の trim を行う
     */
    public function test_trimmingIfConfigIsNormal()
    {
        Storage::fake('public');

        //change config (envを変更できないため、設定ファイルを直接変更する)
        config(['excel.trim' => 'normal']);

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_all_formula_and_out_of_scope.xlsx');
        $originalName = 'xls_all_types_all_formula_and_out_of_scope.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xlsx',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(3, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '　');    //全角は除去されない
        $this->assertEquals($lastInsertedRecords[1]->test_column_varchar, '　 　');  //頭の半角スペースのみ除去される
        $this->assertEquals($lastInsertedRecords[2]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[2]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[2]->test_column_varchar, '　文　字 列');    //※後ろの半角スペースのみ除去される
        $this->assertEquals($lastInsertedRecords[2]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[2]->test_column_datetime, '2020-07-29 17:00:00');
    }

    /**
     * No trimming if config is false
     * 設定が false の場合 trim しない
     */
    public function test_trimmingIfConfigIsFalse()
    {
        Storage::fake('public');

        //change config (envを変更できないため、設定ファイルを直接変更する)
        config(['excel.trim' => 'false']);

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_all_formula_and_out_of_scope.xlsx');
        $originalName = 'xls_all_types_all_formula_and_out_of_scope.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'normal';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // files table
        $this->assertDatabaseHas(
            'files',
            [
                'datasource_id' => 1,
                'table_name' => 'xls_test_all_types',
                'original_name' => $originalName,
                'sheet_name' => $sheetName,
                'extension' => 'xlsx',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(5, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, ' ');
        $this->assertEquals($lastInsertedRecords[1]->test_column_varchar, '　');
        $this->assertEquals($lastInsertedRecords[2]->test_column_varchar, ' 　 　');
        $this->assertEquals($lastInsertedRecords[3]->test_column_varchar, "\n");
        $this->assertEquals($lastInsertedRecords[4]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[4]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[4]->test_column_varchar, '　文　字 列 ');
        $this->assertEquals($lastInsertedRecords[4]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[4]->test_column_datetime, '2020-07-29 17:00:00');
    }

    /**
     * File size test - huge records (10,000 records of csv file)
     * ファイルサイズのテスト　(1万件)
     */
    public function test_fileSize_hugeFileRecords10000()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $path = base_path('tests/misc/excel/xls_all_types_filesize_10000.xlsx');
        $originalName = 'xls_all_types_filesize_10000.xlsx';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'huge_records';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200)
            ->assertJsonFragment(
                [
                    'code' => 10,
                    'file_name' => $originalName,
                    'sheet_name' => $sheetName,
                    'mode' => '追加'
                ]
            );

        // Get xls_test_all_types record in the files table
        $fileRecord = DB::table('files')->orderBy('created_at', 'desc')->get()->first();
        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(10000, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        // $this->assertEquals($lastInsertedRecords[0]->file_id, ?);
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 1);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '1970-01-01');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文字列1');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 0.01);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-01-01 00:00:00');
        $this->assertEquals($lastInsertedRecords[9999]->test_column_bigint, 10000);
        $this->assertEquals($lastInsertedRecords[9999]->test_column_date, '1997-05-18');
        $this->assertEquals($lastInsertedRecords[9999]->test_column_varchar, '文字列10000');
        $this->assertEquals($lastInsertedRecords[9999]->test_column_decimal, 100.0);
        $this->assertEquals($lastInsertedRecords[9999]->test_column_datetime, '2020-01-07 22:39:00');

        // The created_at of the data of the uploaded file (data with the same file_id) must be the same.
        // The created_at of the uploaded data must match the created_at of the files table.
        $this->assertEquals($lastInsertedRecords[0]->created_at, $fileRecord->created_at);
        $this->assertEquals($lastInsertedRecords[9999]->created_at, $fileRecord->created_at);

        // Delete data for next test
        $files = DB::table('files')
            ->where('datasource_id', $datasourceId)
            ->where('original_name', $originalName)
            ->delete();
    }
}
