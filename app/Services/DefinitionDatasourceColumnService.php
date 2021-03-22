<?php

namespace App\Services;

use App\Models\Table;
use App\Models\TableColumns;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Log;
use DB;
use Validator;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\ValidationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\Rule;

/**
 * DefinitionDatasourceColumnService
 */
class DefinitionDatasourceColumnService
{

    /**
     * Add definition datasource columns
     * This method doesn't use DB transaction. Need it in the caller.
     *
     * @param array $requestData
     * @return App\Models\DatasourceColumn
     * @throws App\Exceptions\ValidationException;
     */
    public function add($requestData): DatasourceColumns
    {
        // Create new m_datasource_column record
        $datasourceColumn = $this->addMDatasourceColumn($requestData);

        return $datasourceColumn;
    }

    /**
     * Validate new definition datasource column data
     *
     * @param array $requestData
     * @param boolean $strict Strict validation for before saving. default: true
     * @throws App\Exceptions\ValidationException;
     */
    public function validateForNew($requestData, $strict = true)
    {
        // switch strict validation
        // for datasource_id
        $datasourceIdValidations = [
            'integer',
            'digits_between:1,11',
            'min:1',
        ];
        if ($strict === true) {
            //先頭に必須チェック追加
            array_unshift($datasourceIdValidations, 'required');
            //最後に存在チェックを追加
            $datasourceIdValidations[] = 'exists:m_datasources,id';
        }

        // for table_column_id
        $tableColumnIdValidations = [
            'integer',
            'digits_between:1,11',
            'min:1',
        ];
        if ($strict === true) {
            //先頭に必須チェック追加
            array_unshift($tableColumnIdValidations, 'required');
            //最後に unique チェックを追加
            $tableColumnIdValidations[] = Rule::unique('m_datasource_columns')->where('datasource_id', $requestData['datasource_id'])->whereNull('deleted_at');
        }

        //Validation
        $validator = Validator::make($requestData, [
            'datasource_id'             => $datasourceIdValidations,
            'datasource_column_number'  => 'required|integer|digits_between:1,5|min:1|max:16384',
            'datasource_column_name'    => 'required|string|max:255',
            'table_column_id'           => $tableColumnIdValidations,
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->toArray());
        }
    }

    /**
     * Add new definition table column data in m_datasource_columns
     *
     * @param array $requestData
     * @return App\Models\DatasourceColumn
     */
    public function addMDatasourceColumn($requestData): DatasourceColumns
    {
        // Validation
        $strictValidation = true;
        $this->validateForNew($requestData, $strictValidation);

        // Create new m_datasource_column record
        $datasourceColumn = new DatasourceColumns();
        $datasourceColumn->datasource_id                 = $requestData['datasource_id'];
        $datasourceColumn->datasource_column_number      = $requestData['datasource_column_number'];
        $datasourceColumn->datasource_column_name        = $requestData['datasource_column_name'];
        $datasourceColumn->table_column_id               = $requestData['table_column_id'];
        $datasourceColumn->save();

        return $datasourceColumn;
    }
}
