<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\File;
use App\Models\Datasource;
use App\Imports\DataImport;
use Illuminate\Http\Request;
use App\Imports\CsvDataImport;
use App\Models\DatasourceColumns;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CsvFileUploadException;
use App\Exceptions\UnsupportedFileUploadException;
use App\Exceptions\NoDataOnUploadedExcelFileException;
use App\Exceptions\SemiNormalException;
use App\Services\UploadedFileService;
use App\Services\FileService;

class UploadedFileController extends Controller
{
    protected $UploadedFileService;
    protected $FileService;

    const SUPPORTED_EXTENSION_AND_MIME = [
        // extension => mime
        'xls' => 'xls',
        'xlsx' => 'xlsx',
        // mime: Laravel $file->extension() returns "xlsx" for xlsm
        'xlsm' => 'xlsx',
        // mime: Laravel $file->extension() returns "txt" for text/csv, so filename is not 'csv', returns error
        'csv' => 'txt'
    ];
    private const FILE_LIST_LIMIT = 10;
    private const START_ROW_MIN = 1;
    private const START_ROW_MAX = 1048576;
    private const END_ROW_MIN = 0;
    private const END_ROW_MAX = 1048576;

    public function __construct(
        UploadedFileService $UploadedFileService,
        FileService $FileService
    ) {
        $this->UploadedFileService = $UploadedFileService;
        $this->FileService = $FileService;
    }
    /**
     * display the index page
     */
    public function index()
    {
        $data = $this->searchFiles();
        $datasources = Datasource::get(['id', 'datasource_name']);

        return view('uploaded_file.index', ['data' => $data, 'datasources' => $datasources]);

        // return view('upload.index');
    }

    /**
     * Upload File from Upload Window
     *
     * @group      File Upload
     * @queryParam file required file, The file that needs to be uploaded. Example: file
     * @queryParam sheet_name required string, Specific sheet name of excel file. Example: sheet_name
     * @queryParam datasource_id required int, 'm_datasources'. Example: 1
     * @queryParam start_row required int, Initial row id to start reading file. Example: 1
     * @queryParam end_row required int, Last row id to stop reading file. Example: 10
     * @queryParam mode required File uploading mode. 'append' or 'replace'. Example: append
     *
     * @param        Request $request
     * @return       Response
     * @responseFile apidocs/responses/uploads/upload.file.json
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file'          => 'required|file',
                'sheet_name'    => 'required',
                'datasource_id' => 'required|integer',
                'start_row'     => 'required|integer|min:' . self::START_ROW_MIN . '|max:' . self::START_ROW_MAX,
                'end_row'       => 'required|integer|min:' . self::END_ROW_MIN . '|max:' . self::END_ROW_MAX,
                'mode'          => 'required|in:append,replace'
            ]
        );

        if ($validator->fails()) {
            // meta部分を生成
            $meta = [
                'code' => 400,
                'error_code'  => 10,
                'file_name'     => $request->file() != null ? $request->file('file')->getClientOriginalName() : '',
                'sheet_name'    => isset($request->sheet_name) ? $request->sheet_name : '',
            ];

            // error_summary部分を生成
            $error_summary = [
                'error_message' => trans('error_message_index.parameter_error'),
                'error_count'   => count($validator->errors())
            ];

            // error_details部分を生成
            $error_details = array();
            $error_list = collect($validator->errors());
            foreach ($error_list as $errors) {
                $error_details = array_merge($error_details, $errors);
            }

            $response_data = [
                'meta'    => $meta,
                'error_summary' => $error_summary,
                'error_details'  => $error_details
            ];
            Log::info($response_data);
            return response()->json($response_data, 400, ['Content-Type' => 'text/json'], JSON_UNESCAPED_UNICODE);
        }

        $datasource_id = $request->datasource_id;

        // カラムマッピングとテーブル定義からデータを取得
        $data_column_mappings = DatasourceColumns::join('m_table_columns AS mtd', 'mtd.id', '=', 'm_datasource_columns.table_column_id')
            ->where('m_datasource_columns.datasource_id', $datasource_id)
            ->orderBy('m_datasource_columns.datasource_column_number')
            ->join('m_tables AS mt', 'mt.id', '=', 'mtd.table_id')
            ->get(
                [
                    'm_datasource_columns.datasource_column_number',
                    'mt.table_name',
                    'mtd.column_name',
                    'mtd.column_name_alias',
                    'mtd.data_type',
                    'mtd.length',
                    'mtd.maximum_number',
                    'mtd.decimal_part',
                    'mtd.validation',
                ]
            );

        // make validation rule array and attribute array
        $validation_rule = array_pad(array(), $data_column_mappings->count(), '');
        $attributes = array_pad(array(), $data_column_mappings->count(), '');
        foreach ($data_column_mappings as $data_column_mapping) {
            if ($data_column_mapping->validation != null) {
                $validation_rule[$data_column_mapping->datasource_column_number - 1] = $data_column_mapping->validation;
                $attributes[$data_column_mapping->datasource_column_number - 1] = $data_column_mapping->column_name_alias;
            }
        }

        $table_name = $data_column_mappings->unique('table_name')->first()->table_name;
        $file = $request->file('file');
        $original_name = $file->getClientOriginalName();

        $mime = $file->extension(); //mimeから判断
        $data = [];
        $data['datasource_id'] = $datasource_id;
        $data['original_name'] = $original_name;
        $data['name'] = md5_file($file);
        $data['extension'] = $file->getClientOriginalExtension();   //ファイル名についてる拡張子
        $data['file_name'] = $data['name'] . '.' . $data['extension'];
        $data['sheet_name'] = $request->sheet_name;
        $data['mime'] = $file->getClientMimeType();
        $data['path'] = $request->file->storeAs('public/material_files', $data['file_name']);
        $data['url'] = $request->root() . '/storage/material_files/' . $data['file_name'];
        $data['table_name'] = $table_name;
        $data['uploaded_by'] = auth()->id();
        $data['user_agent'] = $request->header('User-Agent');
        $data['ip_address'] = $request->ip();

        $mode = $request->mode == "append" ? "追加" : "洗い替え";
        $meta = [
            'file_name' => $data['original_name'],
            'sheet_name' => $data['sheet_name'],
            'mode' => $mode
        ];

        $start_row = $request->start_row;
        $end_row = $request->end_row;

        try {
            // check extension
            if (isset(self::SUPPORTED_EXTENSION_AND_MIME[$data['extension']]) == false
                || self::SUPPORTED_EXTENSION_AND_MIME[$data['extension']] != $mime
            ) {
                throw new UnsupportedFileUploadException();
            }

            DB::transaction(
                function () use (
                    $data,
                    $file,
                    $data_column_mappings,
                    $validation_rule,
                    $start_row,
                    $end_row,
                    $attributes,
                    $mode
                ) {

                    $sheet_name = $data['sheet_name'];
                    $sameNameFileIds =  $this->UploadedFileService->findFilesByDatasourceIdAndOriginalName($data['datasource_id'], $data['original_name'], $sheet_name);

                    if ($mode == "洗い替え") {
                        if ($sameNameFileIds->count() == 1) {
                            // データソースID、ファイル名、シート名が同じファイルが既に1つアップロードされている場合
                            $this->FileService->deleteRecordsFromRawTableAndFilesTable($sameNameFileIds[0]);
                        } elseif ($sameNameFileIds->count() >= 2) {
                            // データソースID、ファイル名、シート名が同じファイルが既に2つ以上アップロードされている場合
                            throw new SemiNormalException('ファイル名とシート名が同じデータがすでに複数アップロードされているため、洗い替えできません。すでにアップロードされているデータを確認するか、ファイルを見直してください。');
                        }
                    }

                    // Create an instance, Assign attributes and Save the data on files table.
                    $model = File::create($data);

                    $chunkSize = config('excel.chunk_size');
                    $data_import = null;
                    if ($end_row == 0) {
                        // 最終行までインポートする場合
                        // TODO DataImportを切り替える
                    } else {
                        // 指定行までインポートする場合
                        // TODO DataImportを切り替える
                    }
                    Log::debug(sprintf("PRE IMPORT[%s]: MEMORY USAGE %0.2fMB", $data['original_name'], round(memory_get_usage() / 1024 / 1024)));

                    if ($data['extension'] == 'csv') {
                        // if extension is csv
                        // get beginning of the content
                        $csvContent = file_get_contents($file, null, null, 0, 1024);
                        $bom = hex2bin('EFBBBF');
                        $csvContent = preg_replace("/^{$bom}/", '', $csvContent);
                        if (empty($csvContent)) {
                            throw new CsvFileUploadException('CSVファイルが空です');
                        }
                        // detect Encoding (detect by only first 1024 byte)
                        $encoding = mb_detect_encoding($csvContent);
                        $data_import = new CsvDataImport(
                            $model,
                            $data_column_mappings,
                            $validation_rule,
                            $start_row,
                            $chunkSize,
                            $attributes,
                            $encoding
                        );
                    } else {
                        // extension is excel
                        $data_import = new DataImport(
                            $model,
                            $data_column_mappings,
                            $sheet_name,
                            $validation_rule,
                            $start_row,
                            $chunkSize,
                            $attributes
                        );
                    }
                    $data_array = $data_import->importToArray($file);
                    // バリデーションエラー発生の確認
                    $failures = $data_import->failures();
                    if (count($failures) > 0) {
                        throw new ValidationException($failures);
                    }
                    // DB Insert
                    $this->insertFromArray($data_array, $data['table_name']);
                    Log::debug(sprintf("AFTER IMPORT: MEMORY USAGE %0.2fMB", round(memory_get_usage() / 1024 / 1024)));
                    Log::debug(sprintf("AFTER IMPORT: MEMORY PEAK USAGE %0.2fMB", round(memory_get_peak_usage() / 1024 / 1024)));
                }
            );

            //アップロード成功
            $meta['code'] = 10;
            $file_list = array_column(File::select(['original_name'])->latest()->limit(self::FILE_LIST_LIMIT)->get()->toArray(), 'original_name');
            $response_data = [
                'meta'    => $meta,
                'file_list' => $file_list,
            ];
            Log::info($response_data);
            return response()->json($response_data, 200, ['Content-Type' => 'text/json'], JSON_UNESCAPED_UNICODE);
        } catch (UnsupportedFileUploadException $e) {
            Log::error($e->getMessage());
            // meta部分を生成
            $meta = [
                'code' => 400,
                'error_code'  => 10,
                'file_name'     => $data['original_name'],
                'sheet_name'    => $data['sheet_name'],
            ];
            // error_summary部分を生成
            $error_summary = [
                'error_message' => trans('error_message_index.parameter_error'),
                'error_count'   => 1
            ];
            // error_details部分を生成
            $error_details = [trans('uploaded_file_index.unsupported_file_uploaded')];
            return response()->json(
                [
                    'meta'    => $meta,
                    'error_summary' => $error_summary,
                    'error_details'  => $error_details
                ],
                400,
                ['Content-Type' => 'text/json'],
                JSON_UNESCAPED_UNICODE
            );
        } catch (CsvFileUploadException $e) {
            Log::error($e->getMessage());
            // meta部分を生成
            $meta = [
                'code' => 400,
                'error_code'  => 10,
                'file_name'     => $data['original_name'],
                'sheet_name'    => $data['sheet_name'],
            ];
            // error_summary部分を生成
            $error_summary = [
                'error_message' => trans('error_message_index.parameter_error'),
                'error_count'   => 1
            ];
            // error_details部分を生成
            $error_details = [$e->getMessage()];
            return response()->json(
                [
                    'meta'    => $meta,
                    'error_summary' => $error_summary,
                    'error_details'  => $error_details
                ],
                400,
                ['Content-Type' => 'text/json'],
                JSON_UNESCAPED_UNICODE
            );
        } catch (NoDataOnUploadedExcelFileException $e) {
            Log::error($e->getMessage());
            return response()->json(
                [
                    'code'    => 500,
                    'error_message' => trans('uploaded_file_index.no_data_in_excel_file'),
                    'data'    => null
                ],
                422,
                ['Content-Type' => 'text/json'],
                JSON_UNESCAPED_UNICODE
            );
        } catch (ValidationException $e) {
            $meta['code'] = 400;
            $meta['error_code'] = 20;
            $failures = $e->failures();
            $error_summary = [
                "error_message" => trans('error_message_index.validation_error'),
                "error_count"   => count($failures)
            ];
            $error_details = array();

            foreach ($failures as $failure) {
                array_push(
                    $error_details,
                    [
                        "row"           => $failure->row(),
                        "column_name"   => $failure->attribute(),
                        "message"       => collect($failure->errors())
                    ]
                );
            }

            if (!is_null($e->stopFlag())) {
                array_push(
                    $error_details,
                    [
                        "row" =>  null,
                        "column_name" => null,
                        "message" => trans('error_details_message.stop_process_by_validation_error')
                    ]
                );
            }

            $response_data = [
                'meta'      => $meta,
                'error_summary'   => $error_summary,
                'error_details'   => $error_details,
            ];
            Log::info($response_data);
            return response()->json($response_data, 400, ['Content-Type' => 'text/json'], JSON_UNESCAPED_UNICODE);
        } catch (SemiNormalException $e) {
            Log::debug($e);
            Log::error($e->getMessage());
            $meta['code'] = 20;
            $meta['message'] = $e->getMessage();
            return response()->json(
                [
                    'meta'    => $meta,
                ],
                200,
                ['Content-Type' => 'text/json'],
                JSON_UNESCAPED_UNICODE
            );
        } catch (Exception $e) {
            Log::debug($e);
            Log::error($e->getMessage());
            return response()->json(
                [
                    'code'    => 500,
                    'error_message' => trans('uploaded_file_index.unexpected_system_error'),
                    'data'    => null
                ],
                500,
                ['Content-Type' => 'text/json'],
                JSON_UNESCAPED_UNICODE
            );
        }
    }

    /**
     * Delete uploaded file and data
     */
    public function destroy(Request $request)
    {
        // ini_set( 'memory_limit', '512M' );
        // set_time_limit( 1800 );

        $id = $request->file_id;

        // delete data
        $file = File::findOrFail($id);
        DB::table($file->table_name)->where('file_id', $id)->delete();

        // delete file data
        $file->delete();

        // update page
        $data = $this->searchFiles();
        $datasources = Datasource::get(['id', 'datasource_name']);

        return view('uploaded_file.index', ['data' => $data, 'datasources' => $datasources]);
    }

    /**
     * Return uploaded file list except not deleting
     * Return uploaded file's list and deleted_at is not null
     *
     * @return App\Models\File
     */
    private function searchFiles()
    {
        $query = File::whereNull('deleted_at');
        $data = $query->get();

        return $data;
    }

    /**
     * Insert record line by line
     *
     * @param array  $data_array array of specific table data
     * @param string $table_name specific table name
     */
    private function insertFromArray(array $data_array, string $table_name)
    {
        // データをひとつずつDB登録
        foreach ($data_array as $row) {
            DB::table($table_name)->insert($row);
        }
    }
}
