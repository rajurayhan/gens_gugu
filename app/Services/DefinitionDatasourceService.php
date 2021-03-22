<?php

namespace App\Services;

use App\Models\Datasource;
use Log;
use DB;
use Validator;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\ValidationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\Rule;

/**
 * DefinitionDatasourceService
 */
class DefinitionDatasourceService
{
    /**
     * Add definition Datasource
     * This method doesn't use DB transaction. Need it in the caller.
     *
     * @param array $requestData
     * @return App\Models\Datasource
     * @throws App\Exceptions\ValidationException;
     */
    public function add($requestData): Datasource
    {
        // Create new m_datasource record
        $data_source = $this->addMDatasource($requestData);

        return $data_source;
    }

    /**
     * Validate new definition datasource data
     *
     * @param array $requestData
     * @param boolean $strict Strict validation for before saving. default: true
     * @throws App\Exceptions\ValidationException;
     */
    public function validateForNew($requestData, $strict = true)
    {
        // switch strict validation
        $tableIdValidations = [
            'integer',
            'digits_between:1,11',
            'min:1',
        ];
        if ($strict === true) {
            //先頭に必須チェック追加
            array_unshift($tableIdValidations, 'required');
            //最後に m_tables 存在チェックを追加
            $tableIdValidations[] = 'exists:m_tables,id';
        }

        // Validation
        $validator = Validator::make($requestData, [
            'datasource_name'       => [
                'required',
                'string',
                'max:255',
                Rule::unique('m_datasources', 'datasource_name')->whereNull('deleted_at'),
            ],
            'table_id'              => $tableIdValidations,
            'starting_row_number'   => 'required|integer|digits_between:1,7|min:1|max:1048576',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->toArray());
        }
    }

    /**
     * Add new definition table data in m_datasource
     *
     * @param array $requestData
     * @return App\Models\Datasource
     * @throws App\Exceptions\ValidationException;
     */
    public function addMDatasource($requestData): Datasource
    {
        // Validation
        $strictValidation = true;
        $this->validateForNew($requestData, $strictValidation);

        // Create new m_datasource record
        $data_source     = new Datasource();
        $data_source->datasource_name       =  $requestData['datasource_name'];
        $data_source->table_id              =  $requestData['table_id'];
        $data_source->starting_row_number   =  $requestData['starting_row_number'];
        $data_source->save();

        return $data_source;
    }
}
