<?php

namespace App\Imports;

use App\Services\CastingImportData;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class SelectedSheetImport implements ToModel, WithStartRow, WithValidation, SkipsOnFailure, WithCustomCsvSettings
{
    use Importable, SkipsFailures;

    private const VALIDATION_ERROR_UPPER_LIMIT = 99;

    private $imported_data = array();

    private $file;
    private $data_column_mappings;
    private $validation_rule;
    private $start_row;
    private $attributes;
    private $encoding;

    public function __construct($file, $data_column_mappings, $validation_rule, $start_row, $attributes, $encoding)
    {
        $this->file = $file;
        $this->data_column_mappings = $data_column_mappings;
        $this->validation_rule = $validation_rule;
        $this->start_row = $start_row;
        $this->attributes = $attributes;
        $this->encoding = $encoding;
    }

    public function model(array $row)
    {
        if (mb_strlen(implode($row)) <= 0) {
            return null;
        }

        $casting_import_data = resolve(CastingImportData::class);
        // Log::debug('$row: ' . collect($row));

        $column_count = count($row);
        try {
            $data = array();

            foreach ($this->data_column_mappings as $data_column_mapping) {
                if ($column_count < $data_column_mapping->datasource_column_number) {
                    break;
                }

                $cell_value = $row[$data_column_mapping->datasource_column_number - 1];

                //trim data (depend on env setting)
                $needTrim = config('excel.trim');
                if ($needTrim == 'full') {
                    //全角スペースも含めて trim
                    $cell_value = preg_replace('/\A[\x00\s]++|[\x00\s]++\z/u', '', $cell_value);
                } elseif ($needTrim == 'normal') {
                    //PHP通常のtrim
                    $cell_value = trim($cell_value);
                }

                // If a excel function comes, value is input as null
                if (mb_substr($cell_value, 0, 1) == '=') {
                    $data[$data_column_mapping->column_name] = null;
                } else {
                    $data[$data_column_mapping->column_name] = $casting_import_data->castData(
                        $cell_value,
                        $data_column_mapping
                    );
                }
            }

            if (!empty(array_filter($data))) {
                //データがある場合のみ(数式のみの場合、空の場合、対象外にデータが入っている場合は対象としない)

                $data['file_id'] = $this->file->id;
                $data['file_name'] = $this->file->original_name;
                $data['created_at'] = $this->file->created_at;

                // インポートしたデータはライブラリに返さず（DB登録せず）にimport_arrayへ格納する
                $this->imported_data[] = $data;
            }
            return null;
        } catch (\Throwable $th) {
            throw new \App\Exceptions\Exception(trans('uploaded_file_index.import_failed_exception_error_alert'), 500);
        }
    }

    // インプットしたデータの配列を返す
    public function importedData()
    {
        return $this->imported_data;
    }

    // インポート開始行を指定する
    public function startRow(): int
    {
        return $this->start_row;
    }

    // バリデーションルールを指定する
    public function rules(): array
    {
        return $this->validation_rule;
    }

    // バリデーションルールでエラーが発生した時に呼び出される
    public function onFailure(Failure ...$failures)
    {
        // エラーの内容を保存
        $this->failures = array_merge($this->failures, $failures);
        if (count($this->failures) >= self::VALIDATION_ERROR_UPPER_LIMIT) {
            // エラーの数が一定数を超えた場合、処理を中断する
            throw new ValidationException($this->failures, true);
        }
    }

    // エラーの内容を返す
    public function failures()
    {
        return $this->failures;
    }

    // データのattributeを上書きする
    // ここで指定したattributeがバリデーションエラーのメッセージに使用される
    public function customValidationAttributes()
    {
        return $this->attributes;
    }

    // CSVアップロード時はこの設定が呼ばれる
    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => $this->encoding
        ];
    }
}
