<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use App\Imports\SelectedSheetImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

// 本クラスはインポートするシートを指定するために必要なクラス
// インポートデータをモデルに変換する処理はSelectedSheetImportで実施する
class DataImport implements WithMultipleSheets, WithChunkReading
{
    use Importable, SkipsFailures;

    private $sheet_name;
    private $importClass;
    private $chunkSize;

    public function __construct($file, $data_column_mappings, $sheet_name, $validation_rule, $start_row, $chunkSize, $attributes)
    {
        $this->sheet_name = $sheet_name;
        $this->chunkSize = $chunkSize;

        $encoding = null;
        $this->importClass = new SelectedSheetImport($file, $data_column_mappings, $validation_rule, $start_row, $attributes, $encoding);
    }

    // データを配列にインポートして、その結果を返す
    public function importToArray($file)
    {
        $this->import($file);
        return $this->importClass->importedData();
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    // Selected sheet is imported
    public function sheets(): array
    {
        return [
            $this->sheet_name => $this->importClass
        ];
    }

    public function failures()
    {
        return $this->importClass->failures();
    }
}
