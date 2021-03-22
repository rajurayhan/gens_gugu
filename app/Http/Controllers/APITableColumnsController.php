<?php

namespace App\Http\Controllers;

use App\Libraries\WebApiResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DatasourceColumns;
use App\Models\TableColumns;
use App\Models\Table;
use App\Http\Resources\TDefinition;
use Validator;
use DB;
use Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Exception;
use App\Exceptions\ValidationException;
use App\Services\DefinitionTableColumnService;

class APITableColumnsController extends Controller
{
    protected $DefinitionTableColumnService;

    public function __construct(DefinitionTableColumnService $DefinitionTableColumnService)
    {
        $this->DefinitionTableColumnService = $DefinitionTableColumnService;
    }

    /**
     * Return table column from 'm_table_columns'
     *
     * @group Table Columns
     * @queryParam  id int, The id of table to get sorted column by table_id. Should be grater than 0. Example: 1
     * @param Request $request
     * @return Response
     * @responseFile apidocs/responses/tableColumns/tableColumns.get.json
     *
     */

    public function index(Request $request)
    {
        $tableID = $request->id;
        if (isset($tableID) && !empty($tableID)) {
            $tableDefinitions = TableColumns::where('table_id', $tableID)->get();
        } else {
            $tableDefinitions = TableColumns::get();
        }
        $tableDefinitionsData = $tableDefinitions->map(function ($definition) {
            $definition->tableName = $definition->tables->table_name;
            $definition->unsetRelation('tables');
            return $definition;
        });

        $response_data = [
            'count' => $tableDefinitionsData->count(),
            'columns' => $tableDefinitionsData->toArray()
        ];
        return WebApiResponse::success($response_data);
    }

    /**
     * Update existing record from 'm_table_columns'
     *
     * @group Table Columns
     * @queryParam  id required int, The id of record which to be updated. Should be grater than 0. Example: 1
     * @queryParam  table_id required int, The id of 'm_tables'. Should be grater than 0. Example: 1
     * @queryParam  column_name required string, Name of the column. Example: columnName
     * @queryParam  column_name_alias required string, Name of the column name alias. Example: columnName_alias
     * @queryParam  data_type  required string, Data type of column. Example: INT
     * @queryParam  length  required int, Length of the column. Example: 255
     * @queryParam  maximum_number int, Maximum Number property value for Datatype Double. Example: 10
     * @queryParam  decimal_part int, Decimal Part property value for Datatype Double. Example: 2
     * @queryParam  validation string, Validation rules for this field. Example: required|integer
     *
     * @param Request $request
     * @return Response
     * @responseFile apidocs/responses/tableColumns/tableColumns.update.json
     *
     */


    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'table_id'              => 'required|integer|exists:m_tables,id',
                'column_name'           => [
                    'required',
                    'string',
                    'max:64',
                    Rule::unique('m_table_columns', 'column_name')->whereNull('deleted_at')->where('table_id', $request->table_id)->ignore($request->id),
                ],
                'column_name_alias'     => 'required|string|max:255',
                'data_type'             => [
                    'required',
                    'string',
                    Rule::in($this->DefinitionTableColumnService::TABLE_COLUMN_TYPE_LIST)
                ],
                'length'                => 'required_if:data_type,varchar,bigint|nullable|integer|min:1',
                'maximum_number'        => 'required_if:data_type,decimal|nullable|integer|min:1',
                'decimal_part'          => 'required_if:data_type,decimal|nullable|integer|min:1',
                'validation'            => 'nullable|string'
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors()->toArray(), 422);
        }

        // Additional Validation
        try {
            $this->DefinitionTableColumnService->validateForAll($request->toArray());
        } catch (ValidationException $e) {
            return response()->json($e->failures(), 422);
        }


        $tableDefinitionObj     = new TableColumns();
        $tableDefinition        = $tableDefinitionObj->findOrFail($request->id);

        $tableDefinition->column_name                       = $request->column_name;
        $tableDefinition->column_name_alias                 = $request->column_name_alias;
        $tableDefinition->data_type                         = $request->data_type;
        $tableDefinition->length                            = $request->length;
        $tableDefinition->maximum_number                    = $request->maximum_number;
        $tableDefinition->decimal_part                      = $request->decimal_part;
        $tableDefinition->validation                        = $request->validation;
        if ($request->data_type == 'date' || $request->data_type == 'datetime') {
            $tableDefinition->length                        = null;
            $tableDefinition->maximum_number                = null;
            $tableDefinition->decimal_part                  = null;
            $tableDefinition->validation                    = null;
        }
        // Following 5 changes requires changes on table structures.
        if ($tableDefinition->isDirty('column_name') || $tableDefinition->isDirty('data_type') || $tableDefinition->isDirty('length') || $tableDefinition->isDirty('maximum_number') || $tableDefinition->isDirty('decimal_part')) {
            // Determine if the model or given attribute have been modified using isDirty()
            // You can also check if a particular attribute is changed.
            // example: $tableDefinition->isDirty('column_name')
            if ($tableDefinition->isDirty('data_type')) {
                // If field has data in any records.
                $recordsNotNull = DB::table($tableDefinition->tables->table_name)
                    ->whereNotNull($tableDefinition->getOriginal('column_name'))
                    ->get();
                if (count($recordsNotNull)) {
                    return response()->json(['error' => 'Data Type can not be changed because this field has value.']);
                }
            }

            if ($tableDefinition->isDirty('column_name') && Schema::hasColumn($tableDefinition->tables->table_name, $request->column_name)) {
                return response()->json(['column_name' => ['A Column already exists with this name. Please try a different name']], 422);
            }

            DB::beginTransaction();

            try {
                if ($tableDefinition->isDirty('data_type') || $tableDefinition->isDirty('length') || $tableDefinition->isDirty('maximum_number') || $tableDefinition->isDirty('decimal_part')) {
                    Schema::table($tableDefinition->tables->table_name, function (Blueprint $table) use ($request, $tableDefinition) {

                        switch ($request->data_type) {
                            case 'varchar':
                                $table->string($tableDefinition->getOriginal('column_name'), $request->length)->charset(null)->nullable()->default(null)->change();
                                break;

                            case 'bigint':
                                $table->bigInteger($tableDefinition->getOriginal('column_name'))->charset(null)->nullable()->default(null)->change();
                                break;

                            case 'decimal':
                                $table->decimal($tableDefinition->getOriginal('column_name'), $request->maximum_number, $request->decimal_part)->charset(null)->nullable()->default(null)->change();
                                break;

                            case 'date':
                                $table->date($tableDefinition->getOriginal('column_name'))->charset(null)->nullable()->default(null)->change();
                                break;

                            case 'datetime':
                                $table->dateTime($tableDefinition->getOriginal('column_name'), 0)->charset(null)->nullable()->change();
                                break;

                            default:
                                break;
                        }
                    });
                }

                if ($tableDefinition->isDirty('column_name')) {
                    Schema::table($tableDefinition->tables->table_name, function (Blueprint $table) use ($request, $tableDefinition) {
                        $table->renameColumn($tableDefinition->getOriginal('column_name'), $request->column_name);
                    });
                }

                $tableDefinition->save();
                DB::commit();
                $tableDefinition->tableName = $tableDefinition->tables->table_name;
                $response_data = [
                    'updated' => true,
                    'column'  => $tableDefinition
                ];

                return WebApiResponse::success($response_data);
            } catch (Exception $e) {
                DB::rollback();
                return response()->json($e->getMessage());
            }
        } else {
            $tableDefinition->save();
            $tableDefinition->tableName = $tableDefinition->tables->table_name;
            $response_data = [
                'updated' => true,
                'column'  => $tableDefinition
            ];
            return WebApiResponse::success($response_data);
        }
    }

    /**
     * Insert new record on 'm_table_columns'
     *
     * @group Table Columns
     * @queryParam  table_id required int, The id of 'm_tables'. Should be grater than 0. Example: 1
     * @queryParam  column_name required string, Name of the column. Example: columnName
     * @queryParam  column_name_alias required string, Name of the column name alias. Example: columnName_alias
     * @queryParam  data_type  required string, Data type of column. Example: INT
     * @queryParam  length  required int, Length of the column. Example: 255
     * @queryParam  maximum_number int, Maximum Number property value for Datatype Double. Example: 10
     * @queryParam  decimal_part int, Decimal Part property value for Datatype Double. Example: 2
     * @queryParam  validation string, Validation rules for this field. Example: required|integer
     *
     * @param Request $request
     * @return Response
     * @responseFile apidocs/responses/tableColumns/tableColumns.insert.json
     *
     */

    public function add(Request $request)
    {

        try {
            $tableColumns = DB::transaction(function () use ($request) {
                $params = [
                    'table_id'              => $request->table_id,
                    'column_name'           => $request->column_name,
                    'column_name_alias'     => $request->column_name_alias,
                    'data_type'             => $request->data_type,
                    'length'                => $request->length,
                    'maximum_number'        => $request->maximum_number,
                    'decimal_part'          => $request->decimal_part,
                    'validation'            => $request->validation,
                ];
                return $this->DefinitionTableColumnService->add($params);
            });

            // set tableName
            $tableColumns->tableName = $tableColumns->tables->table_name;

            $response_data = [
                'created' => true,
                'column'  => $tableColumns
            ];
            return WebApiResponse::success($response_data);
        } catch (ValidationException $e) {
            //TODO use WEBApiResponse
            return response()->json($e->failures(), 422);
        } catch (Exception $e) {
            return WebApiResponse::unexpectedError($e);
        }
    }

    /**
     * Delete existing row on 'm_table_columns'
     *
     * @group Table Columns
     * @queryParam  id required int, Id of the record which to be deleted. Example: 1
     *
     * @param Request $request
     * @return Response
     * @response 200 {"success": "Deleted Successfully!"}
     *
     */


    public function delete(Request $request)
    {
        $tableDefinitionObj         = new TableColumns();
        $tableDefinition            = $tableDefinitionObj->findOrFail($request->id);

        $datasource_columns = DatasourceColumns::where('table_column_id', $request->id)->get();
        if (count($datasource_columns)) {
            return response()->json(['error' => 'This table column can not be deleted because it has records on datasource columns.']);
        }
        DB::beginTransaction();
        try {
            Schema::table($tableDefinition->tables->table_name, function (Blueprint $table) use ($tableDefinition) {
                $table->dropColumn($tableDefinition->column_name);
            });

            $tableDefinition->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e);
        }
        return response()->json('Deleted Successfully!');
    }
}
