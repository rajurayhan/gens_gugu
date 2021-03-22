<?php

namespace App\Services;

use App\Models\Table;
use App\Models\TableColumns;
use Log;
use DB;
use Validator;
use Illuminate\Support\Facades\Schema;
use App\Exceptions\ValidationException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\Rule;

/**
 * DefinitionTableColumnService
 */
class DefinitionTableColumnService
{

    /**
     * table column types
     */
    const TABLE_COLUMN_TYPE_LIST = [
        'bigint',
        'date',
        'datetime',
        'decimal',
        'varchar',
    ];

    /**
     * Return column type byte size
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/storage-requirements.html
     * @see https://dev.mysql.com/doc/refman/8.0/en/column-count-limit.html
     */
    public function getTableColumnByte($type, $tableColumn): int
    {
        switch ($type) {
            case 'bigint':
                // 固定 8byte
                return 8;
            case 'date':
                // 固定 3byte
                return 3;
            case 'datetime':
                //※本来は整数部 5 + 小数部 0～3 だがMax値 8byteを返す
                return 8;
            case 'decimal':
                //※本来は桁数によるが、Max値(整数部 65, 小数部 30)の場合の値を返す
                $tableColumn['maximum_number'] = 65;
                $tableColumn['decimal_part'] = 30;

                //整数部、小数部ともに10進数の9桁毎に 4byte 必要。余りの桁は、2桁毎に 1byte 必要。
                //(例: 14桁＝9 あまり 5 … 4byte + 3byte ＝ 7byte)
                //整数部
                $integerDigit = $tableColumn['maximum_number'] - $tableColumn['decimal_part'];
                $integerByte = floor(($integerDigit) / 9) * 4 + ceil(($integerDigit % 9) / 2);
                //小数部
                $fractionalDigit = $tableColumn['decimal_part'];
                $fractionalByte = floor(($fractionalDigit) / 9) * 4 + ceil(($fractionalDigit % 9) / 2);
                return $integerByte + $fractionalByte;
            case 'varchar':
                return $tableColumn['length'] * 4 + ($tableColumn['length'] > 255 ? 2 : 1);
            case 'timestamp':
                //※本来は整数部 4 + 小数部 0～3 だがMax値を返す
                return 7;
            default:
                return 0;
        }
    }

    /**
     * Validation for adding and updating
     *
     * @param $requestData
     * @throws App\Exceptions\ValidationException;
     */
    public function validateForAll(array $requestData)
    {

        //追加チェック
        $errors = [];

        //column_name
        if (preg_match('/[\. ]/', $requestData['column_name'])) {
            $errors['column_name'] = ['テーブルカラム名に利用できない文字が使われています。'];
        }

        //data_type
        switch ($requestData['data_type']) {
            case 'varchar':
                if ($requestData['length'] > 16383) {
                    $errors['length'] = ['varcharの場合、長さは16383以下で指定してください。'];
                }
                break;
            case 'bigint':
                if ($requestData['length'] > 255) {
                    $errors['length'] = ['bigintの場合、長さは255以下で指定してください。'];
                }
                break;
            case 'decimal':
                if ($requestData['maximum_number'] > 65) {
                    $errors['maximum_number'] = ['decimalの場合、全体長（長さ）は65以下で指定してください。'];
                }
                if ($requestData['decimal_part'] > 30) {
                    $errors['decimal_part'] = ['decimalの場合、小数桁は30以下で指定してください。'];
                }
                if ($requestData['maximum_number'] < $requestData['decimal_part']) {
                    $errors["maximum_number"] = ["全体長（長さ）は小数桁よりも大きくしてください。"];
                }
                break;
        }
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    /**
     * Add definition table columns
     * This method doesn't use DB transaction. Need it in the caller.
     *
     * @param array $requestData
     * @return App\Models\TableColumns
     * @throws App\Exceptions\ValidationException;
     */
    public function add($requestData): TableColumns
    {
        // Add new record on m_table_columns
        $tableColumn = $this->addMTableColumn($requestData);

        // Add new defined column on defined table on database
        $this->addTableColumnOnDefinedTable($requestData);

        return $tableColumn;
    }

    /**
     * Validate new definition table column data
     *
     * @param array $requestData
     * @param boolean $strict Strict validation for before saving. default: true
     * @throws App\Exceptions\ValidationException;
     */
    public function validateForNew($requestData, $strict = true)
    {
        // switch strict validation
        // for table_id
        $tableIdValidations = [
            'integer',
        ];
        if ($strict === true) {
            //先頭に必須チェック追加
            array_unshift($tableIdValidations, 'required');
            //最後に存在チェックを追加
            $tableIdValidations[] = 'exists:m_tables,id';
        }

        // for column_name
        $columnNameValidations = [
            'required',
            'string',
            'max:64',
        ];
        if ($strict === true) {
            //最後に unique チェックを追加
            $columnNameValidations[] = Rule::unique('m_table_columns', 'column_name')->whereNull('deleted_at')->where('table_id', $requestData['table_id']);
        }

        // Validation
        $validator = Validator::make(
            $requestData,
            [
                'table_id'              => $tableIdValidations,
                'column_name'           => $columnNameValidations,
                'column_name_alias'     => 'required|string|max:255',
                'data_type'             => [
                    'required',
                    'string',
                    Rule::in(self::TABLE_COLUMN_TYPE_LIST)
                ],
                'length'                => 'required_if:data_type,varchar,bigint|nullable|integer|min:1',
                'maximum_number'        => 'required_if:data_type,decimal|nullable|integer|min:1',
                'decimal_part'          => 'required_if:data_type,decimal|nullable|integer|min:1',
                'validation'            => 'nullable|string'
            ]
        );
        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->toArray());
        }

        // Additional Validation
        $this->validateForAll($requestData);
    }

    /**
     * Add new definition table column data in m_table_columns
     *
     * @param array $requestData
     * @return App\Models\TableColumns
     */
    public function addMTableColumn($requestData): TableColumns
    {
        // Validation
        $strictValidation = true;
        $this->validateForNew($requestData, $strictValidation);

        // Create new m_table_columns record
        $tableColumns                       = new TableColumns();
        $tableColumns->table_id             = $requestData['table_id'];
        $tableColumns->column_name          = $requestData['column_name'];
        $tableColumns->column_name_alias    = $requestData['column_name_alias'];
        $tableColumns->data_type            = $requestData['data_type'];
        $tableColumns->length               = $requestData['length'];
        $tableColumns->maximum_number       = $requestData['maximum_number'];
        $tableColumns->decimal_part         = $requestData['decimal_part'];
        $tableColumns->validation           = $requestData['validation'];
        if ($requestData['data_type'] == 'date' || $requestData['data_type'] == 'datetime') {
            $tableColumns->length           = null;
            $tableColumns->maximum_number   = null;
            $tableColumns->decimal_part     = null;
            $tableColumns->validation       = null;
        }
        $tableColumns->save();

        return $tableColumns;
    }

    /**
     * Add defined column on defined table on database
     * Call this method after creating defined table on table.
     *
     * It's auto committed after calling this method because executing DDL scripts in this method.
     * このメソッドは DDL実行のため、Auto-commit されます。
     *
     * @param array $requestData
     */
    public function addTableColumnOnDefinedTable($requestData)
    {
        // get specific table data
        $table                  = Table::find($requestData['table_id']);
        $tableName              = $table->table_name;

        Schema::table($tableName, function (Blueprint $table) use ($requestData) {
            switch ($requestData['data_type']) {
                case 'varchar':
                    $table->string($requestData['column_name'], $requestData['length'])->nullable()->default(null);
                    break;

                case 'bigint':
                    $table->bigInteger($requestData['column_name'])->charset(null)->nullable()->default(null);
                    break;

                case 'decimal':
                    $table->decimal($requestData['column_name'], $requestData['maximum_number'], $requestData['decimal_part'])->charset(null)->nullable()->default(null);
                    break;

                case 'date':
                    $table->date($requestData['column_name'])->charset(null)->nullable()->default(null);
                    break;

                case 'datetime':
                    $table->dateTime($requestData['column_name'], 0)->charset(null)->nullable()->default(null);
                    break;

                default:
                    throw new Exception('Can not add table column. Given column data type is not authorized.');
                    break;
            }
        });
    }
}
