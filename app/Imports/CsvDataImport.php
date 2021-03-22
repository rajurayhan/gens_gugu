<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use App\Imports\SelectedSheetImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;

/**
 * CSV data import Class
 * CSVデータ インポート用クラス
 * インポート対象となるシートを指定する必要なし
 * ※インポートデータをモデルに変換する処理はSelectedSheetImportで実施する
 */
class CsvDataImport implements WithChunkReading
{
    use Importable, SkipsFailures;

    private $importClass;
    private $chunkSize;

    public function __construct($file, $data_column_mappings, $validation_rule, $start_row, $chunkSize, $attributes, $encoding)
    {
        $this->chunkSize = $chunkSize;

        $this->importClass = new SelectedSheetImport($file, $data_column_mappings, $validation_rule, $start_row, $attributes, $encoding);
    }

    // データを配列にインポートして、その結果を返す
    public function importToArray($file)
    {
        //CSVは SelectedSheetImport を直接呼び出し
        $this->importClass->import($file, null, \Maatwebsite\Excel\Excel::CSV);
        return $this->importClass->importedData();
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function failures()
    {
        return $this->importClass->failures();
    }
}
