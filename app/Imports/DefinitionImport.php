<?php

namespace App\Imports;

use Log;
use App\Imports\DefinitionSelectedSheetImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Select sheet for Definition Excel Importing
 * -> Importing function is in DefinitionSelectedSheetImport.
 *
 * 定義一括アップロード用シート指定クラス
 * インポートデータを変換する処理は DefinitionSelectedSheetImport を参照
 */
class DefinitionImport implements WithMultipleSheets
{
    use Importable;

    /** Selected sheet name 選択したシート名*/
    private $sheet_name;
    /** Using Import Class for selected sheet 選択したシートに使うImportクラス*/
    private $importClass;

    /**
     * Constructor
     *
     * @param string $sheet_name want to import sheet name 取り込みたいシート名
     */
    public function __construct($sheet_name)
    {
        $this->sheet_name = $sheet_name;
        $this->importClass = new DefinitionSelectedSheetImport();
    }

    /**
     * Import Excel Data and get result as array
     * Excelを取り込み、テーブル定義に整形した形で返す
     *
     * @param string|UploadedFile|null $filePath
     * @return array re-formatted definition data
     */
    // データを配列にインポートして、その結果を返す
    public function getData($file)
    {
        $this->import($file);
        return $this->importClass->getImportedData();
    }

    /**
     * Selected sheet is imported
     * 取り込みたいシート
     *
     * @return array
     * @see Maatwebsite\Excel\Concerns\WithMultipleSheets
     */
    public function sheets(): array
    {
        return [
            $this->sheet_name => $this->importClass
        ];
    }
}
