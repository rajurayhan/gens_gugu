<?php

namespace App\Http\Controllers;

use Validator;
use Exception;
use App\Imports\DefinitionImport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ValidationException;
use App\Models\Table;
use App\Models\Datasource;
use App\Exceptions\NoDataOnUploadedExcelFileException;
use App\Libraries\WebApiResponse;
use App\Services\DefinitionTableService;
use App\Services\DefinitionDatasourceService;
use App\Services\DefinitionTableColumnService;
use App\Services\DefinitionDatasourceColumnService;

class APIUploadDefinitionBulkController extends Controller
{

    protected $DefinitionTableService;
    protected $DefinitionDatasourceService;
    protected $DefinitionTableColumnService;
    protected $DefinitionDatasourceColumnService;

    public function __construct(
        DefinitionTableService $DefinitionTableService,
        DefinitionDatasourceService $DefinitionDatasourceService,
        DefinitionTableColumnService $DefinitionTableColumnService,
        DefinitionDatasourceColumnService $DefinitionDatasourceColumnService
    ) {
        $this->DefinitionTableService = $DefinitionTableService;
        $this->DefinitionDatasourceService = $DefinitionDatasourceService;
        $this->DefinitionTableColumnService = $DefinitionTableColumnService;
        $this->DefinitionDatasourceColumnService = $DefinitionDatasourceColumnService;
    }

    /**
     * (private) Get detail messages from validation errors
     *
     * @param array $failures The array from $failures ValidationException->failures()
     * @return array Error detail messages (without field names)
     */
    private function getErrorDetailMessages(array $failures): array
    {
        //Validation Error
        $error_details = [];
        foreach ($failures as $filed => $failure) {
            $failure = is_array($failure) ? $failure : [$failure];
            $error_details = array_merge($error_details, $failure);
        }
        return $error_details;
    }

    /**
     * (private) Get detail messages from validation errors with row number and column detail
     * table_columns/datasource_columnsなど行番号が必須の場合
     *
     * @param array $failures The array from $failures ValidationException->failures()
     * @param int $rowNum Row number which error has occurred
     * @param array $imported Imported excel row data for create detail information of message
     * @return array Error detail messages (without field names)
     */
    private function getErrorDetailsColumnMessages(array $failures, int $rowNum, array $importedData): array
    {
        //Validation Error for column
        $error_details = [];
        foreach ($failures as $failure) {
            $failure = is_array($failure) ? $failure : [$failure];

            // build detail information
            $row = $importedData['table_columns'][$rowNum];
            $detailInfo = [];
            if (!empty($row['datasource_columns']['datasource_column_name'])) {
                $detailInfo[] = 'Excelヘッダ名「' . $row['datasource_columns']['datasource_column_name'] . '」';
            }
            if (empty($detailInfo) && !empty($row['column_name'])) {
                $detailInfo[] = 'テーブルカラム名「' . $row['column_name'] . '」';
            }

            // create error message
            foreach ($failure as $f) {
                $error_details[] = sprintf(
                    "%d行目 %s %s",
                    $rowNum + 7, // add number of header rows
                    empty($detailInfo) ? '' : join(' ', $detailInfo),
                    $f
                );
            }
        }
        return $error_details;
    }

    /**
     * Conditions for importing excel column settings
     * カラム設定の取り込み条件
     *
     * @param array $importedTableColumn imported column settings
     * @return bool
     */
    private function isImportTargetColumn(array $importedTableColumn): bool
    {
        //テーブルカラム名が入力されている場合のみ
        return !empty($importedTableColumn['column_name']);
    }

    /**
     * Upload bulk definition excel file
     *
     * @group Definition Upload
     * @queryParam file required file,The excel file which will include all definition.
     * @queryParam sheet_name required string,The name of the excel file. Example: sheetName
     * @queryParam add_only_datasource User confirm adding datasource only. Set 'true' of 'false'. Example: true
     *
     * @param Request $request
     * @return Response
     * @responseFile apidocs/responses/uploads/upload.definition.json
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file'          => 'required|file|mimes:xls,xlsx',
            'sheet_name'    => 'required',
            'add_only_datasource'    => 'sometimes|required|in:true,false', //sometimes: Requestに該当の項目がある場合のみ内容をチェックする
        ]);

        if ($validator->fails()) {
            return WebApiResponse::parameterValidationError($validator);
        }

        $file = $request->file('file');
        $sheetName = $request->sheet_name;
        $importedData = [];
        $rowNum = null;

        // Import excel data
        $data_import = new DefinitionImport($sheetName);
        $importedData = $data_import->getData($file);

        // Pre-check ----------------
        $needPreCheck = !$request->has('add_only_datasource') || $request->add_only_datasource === 'false';
        if ($needPreCheck && !empty($importedData['table']['table_name']) && Table::where('table_name', $importedData['table']['table_name'])->count() > 0) {
            $targetTable = Table::where('table_name', $importedData['table']['table_name'])->first();
            $targetDatasources = Datasource::where('table_id', $targetTable->id)->get();
            $msg = [];
            $msg[] = '同じテーブルに紐づくデータソースが他にもあります。';
            $msg[] = 'テーブル名：' . $importedData['table']['table_name'];
            $msg[] = 'データソース名：' . join(', ', $targetDatasources->pluck('datasource_name')->all());
            $msg[] = '';
            $msg[] = '追加のデータソースとして設定しますか？';
            return WebApiResponse::successConfirmation(join("\n", $msg), []);
        }

        // Process ----------------
        try {
            $response = null;
            if ($request->has('add_only_datasource') && $request->add_only_datasource === 'true') {
                // データソースのみ追加
                $response = $this->addOnlyDatasource($importedData);
            } else {
                // 通常の追加
                $response = $this->addAllDefinitions($importedData);
            }
            return WebApiResponse::success($response);
        } catch (ValidationException $e) {
            return WebApiResponse::validationErrorForDetails($e->failures());
        } catch (Exception $e) {
            return WebApiResponse::unexpectedError($e);
        }
    }

    /**
     * (private) Validate and add all definitions
     *
     * @param array $importedData
     * @return array response
     */
    private function addAllDefinitions(array $importedData): array
    {
        // Validation ----------------
        $error_details = [];

        // まだ実際にレコードを入れていないので、テーブル間の依存関係はチェックしない
        $strictValidation = false;

        // m_table
        try {
            $this->DefinitionTableService->validateForNew($importedData['table'], $strictValidation);
        } catch (ValidationException $e) {
            // add error messages
            $error_details = array_merge($error_details, $this->getErrorDetailMessages($e->failures()));
        }

        // datasource
        try {
            $this->DefinitionDatasourceService->validateForNew($importedData['datasource'], $strictValidation);
        } catch (ValidationException $e) {
            // add error messages
            $error_details = array_merge($error_details, $this->getErrorDetailMessages($e->failures()));
        }

        // m_table_column
        $tableColumnNames = [];
        $databaseRowSize = 0;
        foreach ($importedData['table_columns'] as $rowNum => $importedTableColumn) {
            //取り込み対象のみ
            if ($this->isImportTargetColumn($importedTableColumn)) {
                try {
                    $this->DefinitionTableColumnService->validateForNew($importedTableColumn, $strictValidation);
                } catch (ValidationException $e) {
                    // add error messages
                    $error_details = array_merge($error_details, $this->getErrorDetailsColumnMessages($e->failures(), $rowNum, $importedData));
                }

                // datasource_column
                if (!empty($importedTableColumn['datasource_columns']['datasource_column_name'])) {
                    //データソースカラム（Excelヘッダ名）が入力されている場合のみ
                    try {
                        $this->DefinitionDatasourceColumnService->validateForNew($importedTableColumn['datasource_columns'], $strictValidation);
                    } catch (ValidationException $e) {
                        // add error messages
                        $error_details = array_merge($error_details, $this->getErrorDetailsColumnMessages($e->failures(), $rowNum, $importedData));
                    }
                }

                // whole columns' validation（全体を通しての入力チェック）
                if (in_array($importedTableColumn['column_name'], $tableColumnNames)) {
                    //カラム名が重複している場合エラー
                    $failures = ["テーブルカラム名が他の列と重複しています。"];
                    $error_details = array_merge($error_details, $this->getErrorDetailsColumnMessages($failures, $rowNum, $importedData));
                } else {
                    $tableColumnNames[] = $importedTableColumn['column_name'];
                }

                //データベースの1行あたりのカラム数を追加
                if (isset($importedTableColumn['data_type'])) {
                    $databaseRowSize += $this->DefinitionTableColumnService->getTableColumnByte($importedTableColumn['data_type'], $importedTableColumn);
                }
            }
        }

        // whole columns' validation（全体を通しての入力チェック）
        $databaseRowSize += 3000; //file_id, file_name, created_by, etc...
        if ($databaseRowSize > $this->DefinitionTableService::DB_ROW_SIZE_LIMIT) {
            $overSize = ceil(($databaseRowSize - $this->DefinitionTableService::DB_ROW_SIZE_LIMIT) / 1000) * 1000;
            $failures = ["データベースに設定できるカラム全体のサイズを超えています。VARCHARの長さを全体で " . ($overSize / 4) . " 程度減らしてください。"];
            $error_details = array_merge($error_details, $this->getErrorDetailMessages($failures));
        }

        //Return error response if errors are existed
        if (!empty($error_details)) {
            throw new ValidationException($error_details);
        }

        //Validationが全て通った後にDB登録 ----------------
        DB::beginTransaction();
        try {
            // m_table
            $createdTable = $this->DefinitionTableService->addMTable($importedData['table']);

            // datasource
            $importedData['datasource']['table_id'] = $createdTable->id;
            $createdDatasource = $this->DefinitionDatasourceService->addMDatasource($importedData['datasource']);

            // m_table_column
            foreach ($importedData['table_columns'] as $rowNum => $importedTableColumn) {
                //取り込み対象のみ
                if ($this->isImportTargetColumn($importedTableColumn)) {
                    $importedTableColumn['table_id'] = $createdTable->id;
                    $createdTableColumn = $this->DefinitionTableColumnService->addMTableColumn($importedTableColumn);

                    // datasource_column
                    if (!empty($importedTableColumn['datasource_columns']['datasource_column_name'])) {
                        //データソースカラム（Excelヘッダ名）が入力されている場合のみ
                        $importedTableColumn['datasource_columns']['datasource_id'] = $createdDatasource->id;
                        $importedTableColumn['datasource_columns']['table_column_id'] = $createdTableColumn->id;
                        $createdDatasourceColumn = $this->DefinitionDatasourceColumnService->addMDatasourceColumn($importedTableColumn['datasource_columns']);
                    }
                }
            }

            DB::commit();

            // create defined table on the database (executing DDL scripts / auto-committed)
            $this->DefinitionTableService->createTable($importedData['table']);

            foreach ($importedData['table_columns'] as $rowNum => $importedTableColumn) {
                //取り込み対象のみ
                if ($this->isImportTargetColumn($importedTableColumn)) {
                    $importedTableColumn['table_id'] = $createdTable->id;
                    $this->DefinitionTableColumnService->addTableColumnOnDefinedTable($importedTableColumn);
                }
            }

            //アップロード成功
            $response = [
                'table_name' => $createdTable->table_name,
                'datasource_name' => $createdDatasource->datasource_name,
            ];

            return $response;
        } catch (ValidationException $e) {
            //Strict Validation Error
            DB::rollBack();
            $error_details = $this->getErrorDetailsColumnMessages($e->failures(), $rowNum, $importedData);
            throw new ValidationException($error_details);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate and add Datasource definition only
     *
     * @param array $importedData
     * @return array $response
     */
    private function addOnlyDatasource(array $importedData): array
    {
        // Get target Table
        $targetTable = Table::where('table_name', $importedData['table']['table_name'])->first();

        // Validation ----------------
        $error_details = [];

        // まだ実際にレコードを入れていないので、テーブル間の依存関係はチェックしない
        $strictValidation = false;

        // datasource
        try {
            $importedData['datasource']['table_id'] = $targetTable->id;
            $this->DefinitionDatasourceService->validateForNew($importedData['datasource'], $strictValidation);
        } catch (ValidationException $e) {
            // add error messages
            $error_details = array_merge($error_details, $this->getErrorDetailMessages($e->failures()));
        }

        // columns
        foreach ($importedData['table_columns'] as $rowNum => $importedTableColumn) {
            //取り込み対象のみ
            if ($this->isImportTargetColumn($importedTableColumn)) {
                if (empty($importedTableColumn['datasource_columns']['datasource_column_name'])) {
                    // データソースカラム名が入力されていない場合は飛ばす
                    continue;
                }

                // Get target Column
                $targetTableColumn = $targetTable->definitions()->where('column_name', $importedTableColumn['column_name']);
                if ($targetTableColumn->count() == 0) {
                    //該当のテーブルカラムが見つからない場合エラー
                    $failures = ["指定されたテーブルカラム名は該当のテーブルに存在しません。"];
                    $error_details = array_merge($error_details, $this->getErrorDetailsColumnMessages($failures, $rowNum, $importedData));
                } else {
                    // datasource_column
                    try {
                        //テーブルカラムIDを追加しておく
                        $importedData['table_columns'][$rowNum]['datasource_columns']['table_column_id'] = $targetTableColumn->first()->id;
                        $this->DefinitionDatasourceColumnService->validateForNew($importedTableColumn['datasource_columns'], $strictValidation);
                    } catch (ValidationException $e) {
                        // add error messages
                        $error_details = array_merge($error_details, $this->getErrorDetailsColumnMessages($e->failures(), $rowNum, $importedData));
                    }
                }
            }
        }

        //Return error response if errors are existed
        if (!empty($error_details)) {
            throw new ValidationException($error_details);
        }

        //Validationが全て通った後にDB登録 ----------------
        DB::beginTransaction();
        try {
            // datasource
            $createdDatasource = $this->DefinitionDatasourceService->addMDatasource($importedData['datasource']);

            // columns
            foreach ($importedData['table_columns'] as $rowNum => $importedTableColumn) {
                if (empty($importedTableColumn['datasource_columns']['datasource_column_name'])) {
                    // データソースカラム名が入力されていない場合は飛ばす
                    continue;
                }

                //取り込み対象のみ
                if ($this->isImportTargetColumn($importedTableColumn)) {
                    // datasource_column
                    $importedTableColumn['datasource_columns']['datasource_id'] = $createdDatasource->id;
                    $createdDatasourceColumn = $this->DefinitionDatasourceColumnService->addMDatasourceColumn($importedTableColumn['datasource_columns']);
                }
            }

            DB::commit();

            //アップロード成功
            $response = [
                'table_name' => $targetTable->table_name,
                'datasource_name' => $createdDatasource->datasource_name,
            ];

            return $response;
        } catch (ValidationException $e) {
            //Strict Validation Error
            DB::rollBack();
            $error_details = $this->getErrorDetailsColumnMessages($e->failures(), $rowNum, $importedData);
            throw new ValidationException($error_details);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
