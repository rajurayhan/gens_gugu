<?php

namespace App\Imports;

use Log;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use App\Libraries\Utility;

class DefinitionSelectedSheetImport implements ToCollection, SkipsOnFailure
{
    use Importable, SkipsFailures;

    /** Re-formatted data 整形済みの取り込みデータ */
    private $imported_data = [
        'table' => [
            'id' => null,
            'table_name' => '',
            'table_name_alias' => '',
        ],
        'table_columns' => [],
        'datasource' => [
            'id' => null,
            'datasource_name'       => '',
            'starting_row_number'   => null,
        ]
    ];


    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get import data as Collection and re-format data for definition
     * Excelからデータ取得＆テーブル定義の形に整形
     *
     * @param Collection $collection
     * @return null;
     *
     * @see Maatwebsite\Excel\Concerns\ToCollection
     */
    public function collection(Collection $rows)
    {
        try {
            //table
            $this->imported_data['table']['id'] = $rows[0][4];
            $this->imported_data['table']['table_name'] = $rows[1][4];
            $this->imported_data['table']['table_name_alias'] = $rows[2][4];

            //datasource
            $this->imported_data['datasource']['id'] = $rows[0][1];
            $this->imported_data['datasource']['datasource_name'] = $rows[1][1];
            $this->imported_data['datasource']['starting_row_number'] = $rows[2][1];

            //table_columns & datasource_columns
            foreach ($rows as $i => $row) {
                // skip headers
                if ($i < 6) {
                    continue;
                }

                $table_columns = [
                    'column_name' => $row[3],
                    'column_name_alias' => $row[4],
                    'data_type' => strtolower($row[5]),
                    'length' => $row[5] != 'DECIMAL' ? $row[6] : null,    // DECIMAL doesn't use length
                    'maximum_number' => $row[5] == 'DECIMAL' ? $row[6] : null,
                    'decimal_part' => $row[7] ?? null,
                    'validation' => $row[8],
                ];

                $datasource_columns = [
                    'datasource_column_alphabet' => $row[0],
                    'datasource_column_number' => empty($row[0]) ? null : Utility::alpha2num($row[0]),
                    'datasource_column_name' => $row[1],
                ];

                // テーブルカラムに対応するデータソースを入れておく
                $table_columns['datasource_columns'] = $datasource_columns;
                $this->imported_data['table_columns'][] = $table_columns;
            }
        } catch (\Throwable $th) {
            Log::info($th);
            throw new \App\Exceptions\Exception(trans('uploaded_file_index.import_failed_exception_error_alert'), 500);
        }

        return null;
    }

    /**
     * Return imputed (Re-formatted) data
     * 整形済みの取り込みデータ
     *
     * @return array
     */
    public function getImportedData()
    {
        return $this->imported_data;
    }
}
