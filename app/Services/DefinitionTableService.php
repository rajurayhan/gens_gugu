<?php

namespace App\Services;

use DB;
use Log;
use Validator;
use App\Models\Table;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\ValidationException;
use Illuminate\Database\Schema\Blueprint;

/**
 * DefinitionTableService
 */
class DefinitionTableService
{
    /**
     * DB ROW SIZE LIMIT
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/column-count-limit.html
     */
    const DB_ROW_SIZE_LIMIT = 65535;

    /**
     * Add definition table
     * This method doesn't use DB transaction. Need it in the caller.
     *
     * @param  array $requestData
     * @return App\Models\Table
     * @throws App\Exceptions\ValidationException;
     */
    public function add($requestData): Table
    {
        // Add new record on m_table
        $table = $this->addMTable($requestData);

        // Create new table on database
        $this->createTable($requestData);

        return $table;
    }

    /**
     * Validate new definition table data
     *
     * @param  array   $requestData
     * @param  boolean $strict      Strict validation for before saving. default: true
     * @throws App\Exceptions\ValidationException;
     */
    public function validateForNew($requestData, $strict = true)
    {
        // Validation
        $validator = Validator::make(
            $requestData,
            [
                'table_name'        => ['required', Rule::unique('m_tables')->whereNull('deleted_at'), 'string', 'max:64'],
                'table_name_alias'  => 'required|string|max:255',
            ]
        );
        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->toArray());
        }
        if (Schema::hasTable($requestData['table_name'])) {
            throw new ValidationException(['table_name' => ['This table can not be created because it is a base table name']]);
        }
    }

    /**
     * Add new definition table data in m_table
     *
     * @param  array $requestData
     * @return App\Models\Table
     */
    public function addMTable($requestData): Table
    {
        // Validation
        $strictValidation = true;
        $this->validateForNew($requestData, $strictValidation);

        // Create new m_table record
        $table                      = new Table();
        $table->table_name          = $requestData['table_name'];
        $table->table_name_alias    = $requestData['table_name_alias'];
        $table->save();

        return $table;
    }

    /**
     * Create new defined table on database
     * It's auto committed after calling this method because executing DDL scripts in this method.
     * このメソッドは DDL実行のため、Auto-commit されます。
     *
     * @param array $requestData
     */
    public function createTable($requestData)
    {
        // Create new specific table
        Schema::create(
            $requestData['table_name'],
            function (Blueprint $table) {
                $table->string('file_name');
                $table->bigInteger('file_id');
                $table->string('created_by')->nullable();
                $table->timestamp('created_at')->nullable();
            }
        );
    }
}
