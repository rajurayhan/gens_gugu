<?php

namespace Tests\Feature;

use DB;
use File;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadedFileControllerForCsvTest extends TestCase
{
    public static $TEST_DATA_PATH = 'storage/tmp/';

    /**
     * Setup
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed --class=TranslationSeeder");
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

    /*

    CSVの仕様
    ・ファイルは1つ以上のレコードからなる。
    ・レコードは改行（CRLF、U+000D U+000A）で区切られる。最後のレコードの後には改行はあってもなくてもいい。
    ・レコードは1つ以上の同じ個数のフィールドからなる。
    ・フィールドはコンマ「,」(U+002C) で区切られる。最後のフィールドの後にはコンマは付けない。
    ・フィールドは、ダブルクォート「"」(U+0022) で囲んでも囲まなくてもよい。
    ・フィールドがコンマ、ダブルクォート、改行を含む場合は、かならずダブルクォートで囲む。また、フィールドに含まれるダブルクォートは2つ並べてエスケープする。

    テストデータをCSV管理すると改行コードがOS依存となるため、テストデータはテストコード内で作成する。
    */

    /**
     * (private) Create Test Data
     *  フィールド内の改行は「(newline)」とする
     *  if you want to set new line in the field, put "(newline)"
     *
     * @param string  $filename
     * @param string  $content
     * @param string  $eol      default "\r\n"
     * @param string  $encode   default "UTF-8"
     * @param boolean $bom      default true
     */
    private function createTestData($filename, $content, $eol = "\r\n", $encode = "UTF-8", $bom = true)
    {
        $path = base_path(static::$TEST_DATA_PATH . $filename);

        // EOL (End of file)
        $content = preg_replace("/\r\n|\r|\n/", $eol, $content);

        // セル内改行 (new line in the cell)
        $content = preg_replace("/\(newline\)/", "\n", $content);

        // タブ (tab)
        $content = preg_replace("/\(tab\)/", "\t", $content);

        // Encode
        $fileData = mb_convert_encoding($content, $encode, "auto");

        // BOM
        if ($encode == 'UTF-8' && $bom) {
            $fileData = "\xEF\xBB\xBF" . $fileData;
        }

        // Put file
        $bytesWritten = File::put($path, $fileData);

        return $path;
    }

    /**
     * (private) Delete Test Data
     *
     * @param string $filename
     */
    private function deleteTestData($filename)
    {
        $path = base_path(static::$TEST_DATA_PATH . $filename);
        File::delete($path);
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
     * Uploadable CSV file (UTF-8 with BOM)
     * 通常のCSVがアップロードできる (UTF-8 with BOM)
     *
     * When save csv file from Excel, usually added BOM.
     */
    public function test_AbleToUploadCSV_UTF8()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_UTF8.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
12345,2020/7/29,2020/7/29 17:00,123.45,文字列
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
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
            'extension' => 'csv',
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

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Uploadable CSV file (UTF-8 no BOM)
     * 通常のCSVがアップロードできる (UTF-8 no BOM)
     */
    public function test_AbleToUploadCSV_UTF8noBOM()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_UTF8_no_BOM.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = false;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
12345,2020/7/29,2020/7/29 17:00,123.45,文字列
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200);

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

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Uploadable CSV file of SJIS
     * 通常のCSVがアップロードできる (SJIS)
     */
    public function test_AbleToUploadCSV_SJIS()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_SJIS.csv';
        $eol = "\r\n";
        $encode = "SJIS";
        $bom = false;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
12345,2020/7/29,2020/7/29 17:00,123.45,文字列
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200);

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

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Uploadable CSV file of Special characters (line break/comma/double-quotation in the cell, Japanese special characters, etc.)
     * セル内に特別な文字が入ったのCSVがアップロードできる （セル内改行、カンマ、ダブルクォーテーション、機種依存文字、半角カタカナ、タブ　など）
     */
    public function test_AbleToUploadCSV_specialCharacters()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_special_characters.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
1000,2020/7/29,2020/7/29 17:00,100.01,"セル内(newline)改行"
1001,2020/7/29,2020/7/29 17:00,101.01,"(newline)先頭に改行"
1002,2020/7/29,2020/7/29 17:00,102.01,"最後に改行(newline)"
1003,2020/7/29,2020/7/29 17:00,103.01,"セルの中に,カンマ,有り"
1004,2020/7/29,2020/7/29 17:00,104.01,",先頭にカンマあり"
1005,2020/7/29,2020/7/29 17:00,105.01,"最後にカンマあり,"
1006,2020/7/29,2020/7/29 17:00,106.01,"セル内に""ダブルコーテーション""あり"
1007,2020/7/29,2020/7/29 17:00,107.01,"""最初にダブルコーテーションあり"
1008,2020/7/29,2020/7/29 17:00,108.01,"最後にダブルコーテーションあり"""
1009,2020/7/29,2020/7/29 17:00,109.01,ﾊﾝｶｸｶﾀｶﾅ
1010,2020/7/29,2020/7/29 17:00,110.01,♂♀　機種依存文字
1011,2020/7/29,2020/7/29 17:00,111.01,長い文字２００文字０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０
1012,2020/7/29,2020/7/29 17:00,112.01,セル内(tab)タブ
1013,2020/7/29,2020/7/29 17:00,113.01,(tab)先頭にタブ
1014,2020/7/29,2020/7/29 17:00,114.01,最後にタブ(tab)
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);


        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200);

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(15, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, "セル内\n改行");
        $this->assertEquals($lastInsertedRecords[1]->test_column_varchar, "先頭に改行");    // 行頭の改行は除去される
        $this->assertEquals($lastInsertedRecords[2]->test_column_varchar, "最後に改行");    // 行末の改行は除去される
        $this->assertEquals($lastInsertedRecords[3]->test_column_varchar, "セルの中に,カンマ,有り");
        $this->assertEquals($lastInsertedRecords[4]->test_column_varchar, ",先頭にカンマあり");
        $this->assertEquals($lastInsertedRecords[5]->test_column_varchar, "最後にカンマあり,");
        $this->assertEquals($lastInsertedRecords[6]->test_column_varchar, "セル内に\"ダブルコーテーション\"あり");
        $this->assertEquals($lastInsertedRecords[7]->test_column_varchar, "\"最初にダブルコーテーションあり");
        $this->assertEquals($lastInsertedRecords[8]->test_column_varchar, "最後にダブルコーテーションあり\"");
        $this->assertEquals($lastInsertedRecords[9]->test_column_varchar, "ﾊﾝｶｸｶﾀｶﾅ");
        $this->assertEquals($lastInsertedRecords[10]->test_column_varchar, "♂♀　機種依存文字");
        $this->assertEquals($lastInsertedRecords[11]->test_column_varchar, "長い文字２００文字０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０１２３４５６７８９０");
        $this->assertEquals($lastInsertedRecords[12]->test_column_varchar, "セル内\tタブ");
        $this->assertEquals($lastInsertedRecords[13]->test_column_varchar, "先頭にタブ");    // 行頭のタブは除去される
        $this->assertEquals($lastInsertedRecords[14]->test_column_varchar, "最後にタブ");    // 行末のタブは除去される

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Abnormal csv format -  file is empty
     * ファイル内容が空の場合
     */
    public function test_abnormalCsvFormat_emptyFile()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_content_empty.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                "meta" => [
                    'code' => 400,
                    'error_code'  => 10,
                    'file_name'     => $originalName,
                    'sheet_name'    => $sheetName,
                ],
                "error_summary" => [
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_count'   => 1
                ],
                "error_details" => ["CSVファイルが空です"]
                ]
            );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecordCount = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(0, count($lastInsertedRecordCount));

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Abnormal csv format -  newline code is LF instead of CRLF
     * CSVの改行がLFでなくCRLF（正しく取り込まれる）
     */
    public function test_abnormalCsvFormat_newlineCodeIsLF()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_abnormal_newline_LF.csv';
        $eol = "\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
10000,2020/1/1,2020/1/1 12:00:10,100.01,文字列1
10001,2020/1/2,2020/1/2 12:00:20,101.01,文字列2
10002,2020/1/3,2020/1/3 12:00:30,102.01,文字列3
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200);

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(3, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 10000);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-01-01');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, "文字列1");
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 100.01);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-01-01 12:00:10');
        $this->assertEquals($lastInsertedRecords[1]->test_column_bigint, 10001);
        $this->assertEquals($lastInsertedRecords[1]->test_column_date, '2020-01-02');
        $this->assertEquals($lastInsertedRecords[1]->test_column_varchar, "文字列2");
        $this->assertEquals($lastInsertedRecords[1]->test_column_decimal, 101.01);
        $this->assertEquals($lastInsertedRecords[1]->test_column_datetime, '2020-01-02 12:00:20');
        $this->assertEquals($lastInsertedRecords[2]->test_column_bigint, 10002);
        $this->assertEquals($lastInsertedRecords[2]->test_column_date, '2020-01-03');
        $this->assertEquals($lastInsertedRecords[2]->test_column_varchar, "文字列3");
        $this->assertEquals($lastInsertedRecords[2]->test_column_decimal, 102.01);
        $this->assertEquals($lastInsertedRecords[2]->test_column_datetime, '2020-01-03 12:00:30');

        //cleanup
        $this->deleteTestData($originalName);
    }


    /**
     * Abnormal csv format -  newline code in the cell is CRLF instead of LF
     * セル内に改行がLFでなくCRLF（正しく取り込まれる）
     */
    public function test_abnormalCsvFormat_newlineCodeInCellIsCRLF()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_abnormal_newline_CRLF_in_cell.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
10000,2020/1/1,2020/1/1 12:00,100.01,"セル内改行
（改行コードがCRLF）
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200);

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 10000);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-01-01');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, "セル内改行\n（改行コードがCRLF）");
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 100.01);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-01-01 12:00:00');

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Abnormal csv format -  misalignment
     * セル内に改行があるのにダブルクォーテーションで囲まれていない（列ズレ）
     */
    public function test_abnormalCsvFormat_misalignment()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_abnormal_misalignment.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
12345,2020/7/29,2020/7/29 17:00,123.45,Misalignmentは
文字ズレの意味
（セル内に改行があるのにダブルクォーテーションを除去）
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);


        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200);

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, "Misalignmentは");
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');
        //2行目の先頭はBIGINTなので取り込まれない → 全てNULLとなるため取り込まれない
        //3行目の先頭はBIGINTなので取り込まれない → 全てNULLとなるため取り込まれない

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Abnormal csv format - Single Quotation enclosure
     * 通常のCSVがアップロードできる (囲み文字が シングルクォーテーションだった場合)
     * ※ ''は囲み文字と認識されないため文字列とみなされ取り込まれない
     */
    public function test_abnormalCsvFormat_singleQuotationEnclosure()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_abnormal_single_quotation_enclosure.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
'列A_BIGINT','列B_DATE','列C_DATETIME','列D_DECIMAL','列E_VARCHAR'
'12345','2020/7/29','2020/7/29 17:00','123.45','文字列'
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(200);

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, null); //''は囲み文字と認識されないため文字列とみなされ取り込まれない
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, null); //''は囲み文字と認識されないため文字列とみなされ取り込まれない
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, "'文字列'");
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, null); //''は囲み文字と認識されないため文字列とみなされ取り込まれない
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, null); //''は囲み文字と認識されないため文字列とみなされ取り込まれない

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Extension test - uploading text file  (ERROR)
     * 拡張子テスト　テキストファイルのアップロード
     */
    public function test_extensionTest_uploadingTextFileShowsError()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_extension_text_file.txt';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
12345,2020/7/29,2020/7/29 17:00,123.45,文字列
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                "meta" => [
                    'code' => 400,
                    'error_code'  => 10,
                    'file_name'     => $originalName,
                    'sheet_name'    => $sheetName,
                ]
                ]
            )
            ->assertJsonFragment(
                [
                "error_summary" => [
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_count'   => 1
                ]
                ]
            )
            ->assertJsonFragment(
                [
                "error_details" => ["未対応のファイルが選択されています。Excelファイルかcsvファイルを選択してください。"]
                ]
            );

        // Get all records in the xls_test_all_types table naosu
        $lastInsertedRecordCount = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(0, count($lastInsertedRecordCount));

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Extension test - uploading csv file but filename is ***.xlsx
     * 拡張子テスト　ファイルの中身はCSVだがファイル名がEXCELの拡張子の場合
     */
    public function test_extensionTest_uploadingCsvFileButContentIsExcel()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_extension_csv_file_but_filename_is_excel.xlsx';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
12345,2020/7/29,2020/7/29 17:00,123.45,文字列
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);


        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                "meta" => [
                    'code' => 400,
                    'error_code'  => 10,
                    'file_name'     => $originalName,
                    'sheet_name'    => $sheetName,
                ],
                "error_summary" => [
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_count'   => 1
                ],
                "error_details" => ["未対応のファイルが選択されています。Excelファイルかcsvファイルを選択してください。"]
                ]
            );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecordCount = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(0, count($lastInsertedRecordCount));

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Extension test - uploading excel file but filename is ***.csv
     * 拡張子テスト　ファイルの中身はExcelだがファイル名がCSVの場合
     */
    public function test_extensionTest_uploadingExcelFileButContentIsCsv()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        // 中身がバイナリファイルなのでGit管理されているデータを利用する（use existed file because this is binary file)
        $path = base_path('tests/misc/csv/xls_all_types_extension_excel_file_but_filename_is_csv.csv');
        $originalName = 'xls_all_types_extension_excel_file_but_filename_is_csv.csv';

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                "meta" => [
                    'code' => 400,
                    'error_code'  => 10,
                    'file_name'     => $originalName,
                    'sheet_name'    => $sheetName,
                ],
                "error_summary" => [
                    'error_message' => 'パラメータエラーが発生しました。',
                    'error_count'   => 1
                ],
                "error_details" => ["未対応のファイルが選択されています。Excelファイルかcsvファイルを選択してください。"]
                ]
            );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecordCount = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(0, count($lastInsertedRecordCount));
    }

    /**
     * Validation test - file has validation error
     * バリデーションテスト バリデーションエラーが有る場合
     */
    public function test_validationTest_fileHasValidationError()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types2_validation_error.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR
12345,2020/7/29,2020/7/29 17:00,123.45,文字列（この行はOK）
aiu,2020/7/29,2020/7/29 17:00,123.45,
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 10; // xls_test_all_types_with_validation table
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                "error_summary" => [
                    "error_message" => "不正なデータがあります。",
                    "error_count" => 2
                ]
                ]
            )
            ->assertJsonFragment(
                [
                "error_details" => [
                    [
                        "row" => 3,
                        "column_name" => "カラム_BIGINT",
                        "message" => [
                            "カラム_BIGINTは整数で指定してください。",
                        ]
                    ],
                    [
                        "row" => 3,
                        "column_name" => "カラム_文字列",
                        "message" => [
                            "カラム_文字列は必ず指定してください。"
                        ]
                    ]
                ]
                ]
            );

        // Get all records in the xls_test_all_types_with_validation table
        $lastInsertedRecordCount = DB::table('xls_test_all_types_with_validation')->get();

        // Check number of 'xls_test_all_types_with_validation' records
        $this->assertEquals(0, count($lastInsertedRecordCount));

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Validation test - validation error is over the limit
     * バリデーションテスト バリデーションエラーが制限を超えた場合停止するか
     */
    public function test_validationTest_validationErrorIsOverTheLimit()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types2_validation_error_over_limit.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = "列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR\n";
        for ($i = 1; $i <= 110; $i++) {
            $testDate = date("Y/m/d", strtotime('+ ' . $i . ' days', strtotime('1900-01-01')));
            //set minus data to first BIGINT column
            $content .= sprintf(
                "%d,%s,%s 0:00,%.2f,文字列%d\n",
                ($i * -1),
                $testDate,
                $testDate,
                ($i * 0.01),
                $i
            );
        }
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 10; // xls_test_all_types_with_validation table
        $sheetName = 'Sheet1';
        $startRow = 2;
        $endRow = 0;
        $mode = 'append';

        // Executing
        $response = $this->callAPI(compact('path', 'originalName', 'datasourceId', 'sheetName', 'startRow', 'endRow', 'mode'));

        // Checking
        // response
        $response->assertStatus(400)
            ->assertJsonFragment(
                [
                "error_summary" => [
                    "error_message" => "不正なデータがあります。",
                    "error_count" => 99
                ]
                ]
            )
            ->assertJsonCount(100, 'error_details')
            ->assertJsonFragment(
                [
                "row" => 2,
                "column_name" => "カラム_BIGINT",
                "message" => [
                    "カラム_BIGINTには、1以上の数字を指定してください。",
                ]
                ]
            )
            ->assertJsonFragment(
                [
                "row" => 100,
                "column_name" => "カラム_BIGINT",
                "message" => [
                    "カラム_BIGINTには、1以上の数字を指定してください。",
                ]
                ]
            );

        //last of error
        $this->assertEquals(
            [
            "row" => null,
            "column_name" => null,
            "message" => "不正なデータが一定数を超えたため、処理を中断しました。",
            ],
            $response->original['error_details'][99]
        );


        // Get all records in the xls_test_all_types_with_validation table
        $lastInsertedRecordCount = DB::table('xls_test_all_types_with_validation')->get();

        // Check number of 'xls_test_all_types_with_validation' records
        $this->assertEquals(0, count($lastInsertedRecordCount));

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * Don't import records which data only in the out of scope
     * 取り込み対象外のカラムのみデータが入っている場合、その行を取り込まない
     */
    public function test_doNotImportRecordsWhichDataOnlyInTheOutOfScope()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_formula_and_out_of_scope.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR,
,,,,,範囲外に値 ※データは全部空文字
,,,, ,列E_VARCHARに半角スペース
,,,,　,列E_VARCHARに全角スペース
,,,, 　 　,列E_VARCHARに全角スペース＆半角スペース混在
,,,,(newline),列E_VARCHARに改行
,,,,(tab),列E_VARCHARにタブ
12345,2020/7/29,2020/7/29 17:00,123.45,　文　字 列 ,VARCHAR内の前後中に半角/全角スペースあり
,,,,,
,,,,,
,,,,,
,,,,,
,,,,,
,,,,,ここに値があっても取り込まれない
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
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
            'extension' => 'csv',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文　字 列');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');

        //cleanup
        $this->deleteTestData($originalName);
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
        $originalName = 'xls_all_types_full_trimming.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR,
,,,, ,列E_VARCHARに半角スペース
,,,,　,列E_VARCHARに全角スペース
,,,, 　 　,列E_VARCHARに全角スペース＆半角スペース混在
,,,,(newline),列E_VARCHARに改行
,,,,(tab),列E_VARCHARにタブ
12345,2020/7/29,2020/7/29 17:00,123.45,　文　字 列 ,VARCHAR内の前後中に半角/全角スペースあり
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
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
            'extension' => 'csv',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(1, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文　字 列');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '2020-07-29 17:00:00');

        //cleanup
        $this->deleteTestData($originalName);
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
        $originalName = 'xls_all_types_normal_trimming.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR,
,,,, ,列E_VARCHARに半角スペース
,,,,　,列E_VARCHARに全角スペース
,,,, 　 　,列E_VARCHARに全角スペース＆半角スペース混在
,,,,(newline),列E_VARCHARに改行
,,,,(tab),列E_VARCHARにタブ
12345,2020/7/29,2020/7/29 17:00,123.45,　文　字 列 ,VARCHAR内の前後中に半角/全角スペースあり
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
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
            'extension' => 'csv',
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

        //cleanup
        $this->deleteTestData($originalName);
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
        $originalName = 'xls_all_types_no_trimming.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = <<<EOF
列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR,
,,,, ,列E_VARCHARに半角スペース
,,,,　,列E_VARCHARに全角スペース
,,,, 　 　,列E_VARCHARに全角スペース＆半角スペース混在
,,,,"(newline)",列E_VARCHARに改行
,,,,(tab),列E_VARCHARにタブあり
12345,2020/7/29,2020/7/29 17:00,123.45,　文　字 列 ,VARCHAR内の前後中に半角/全角スペースあり
EOF;
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);

        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
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
            'extension' => 'csv',
            ]
        );

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(6, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, ' ');
        $this->assertEquals($lastInsertedRecords[1]->test_column_varchar, '　');
        $this->assertEquals($lastInsertedRecords[2]->test_column_varchar, ' 　 　');
        $this->assertEquals($lastInsertedRecords[3]->test_column_varchar, "\n");
        $this->assertEquals($lastInsertedRecords[4]->test_column_varchar, "\t");
        $this->assertEquals($lastInsertedRecords[5]->test_column_bigint, 12345);
        $this->assertEquals($lastInsertedRecords[5]->test_column_date, '2020-07-29');
        $this->assertEquals($lastInsertedRecords[5]->test_column_varchar, '　文　字 列 ');
        $this->assertEquals($lastInsertedRecords[5]->test_column_decimal, 123.45);
        $this->assertEquals($lastInsertedRecords[5]->test_column_datetime, '2020-07-29 17:00:00');

        //cleanup
        $this->deleteTestData($originalName);
    }

    /**
     * File size test - huge records (10,000 records of csv file)
     * ファイルサイズのテスト　(1万件)
     */
    public function test_fileSize_hugeFileRecords10000()
    {
        Storage::fake('public');

        // Set parameters for UploadFile
        $originalName = 'xls_all_types_filesize_10000.csv';
        $eol = "\r\n";
        $encode = "UTF-8";
        $bom = true;
        $content = "列A_BIGINT,列B_DATE,列C_DATETIME,列D_DECIMAL,列E_VARCHAR\n";
        for ($i = 1; $i <= 10000; $i++) {
            $testDate = date("Y/m/d", strtotime('+ ' . ($i - 1) . ' days', strtotime('1900-01-01')));
            $content .= sprintf(
                "%d,%s,%s 0:00,%.2f,文字列%d\n",
                $i,
                $testDate,
                $testDate,
                ($i * 0.01),
                $i
            );
        }
        $path = $this->createTestData($originalName, $content, $eol, $encode, $bom);


        // Set parameters for posting
        $datasourceId = 1;
        $sheetName = 'Sheet1';
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

        // Get all records in the xls_test_all_types table
        $lastInsertedRecords = DB::table('xls_test_all_types')->get();

        // Check number of 'xls_test_all_types' records
        $this->assertEquals(10000, count($lastInsertedRecords));

        // Check table data of 'xls_test_all_types'
        $this->assertEquals($lastInsertedRecords[0]->test_column_bigint, 1);
        $this->assertEquals($lastInsertedRecords[0]->test_column_date, '1900-01-01');
        $this->assertEquals($lastInsertedRecords[0]->test_column_varchar, '文字列1');
        $this->assertEquals($lastInsertedRecords[0]->test_column_decimal, 0.01);
        $this->assertEquals($lastInsertedRecords[0]->test_column_datetime, '1900-01-01 00:00:00');
        $this->assertEquals($lastInsertedRecords[9999]->test_column_bigint, 10000);
        $this->assertEquals($lastInsertedRecords[9999]->test_column_date, '1927-05-19');
        $this->assertEquals($lastInsertedRecords[9999]->test_column_varchar, '文字列10000');
        $this->assertEquals($lastInsertedRecords[9999]->test_column_decimal, 100.0);
        $this->assertEquals($lastInsertedRecords[9999]->test_column_datetime, '1927-05-19 00:00:00');
    }
}
