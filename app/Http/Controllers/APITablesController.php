<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Models\Table;
use App\Models\TableColumns;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use App\Libraries\WebApiResponse;
use App\Libraries\Utility;
use DB;
use Log;
use Validator;
use App\Services\DefinitionTableService;
use App\Exceptions\Exception;
use App\Exceptions\ValidationException;

class APITablesController extends Controller
{

    const GET_SUPPORTED_PARAMETER = [];

    protected $DefinitionTableService;

    public function __construct(DefinitionTableService $DefinitionTableService)
    {
        $this->DefinitionTableService = $DefinitionTableService;
    }

    /**
     * Return table list from 'm_tables'
     *
     * @group Tables
     * @param Request $request
     * @return Response
     * @responseFile apidocs/responses/tables/tables.get.json
     *
     */

    public function index(Request $request)
    {

        $unsupported_params = Utility::getUnsupportParameters($request->all(), self::GET_SUPPORTED_PARAMETER);
        if (count($unsupported_params) > 0) {
            return WebApiResponse::unsupportParameterError($unsupported_params);
        }

        $query  = Table::query();
        $tables = $query->orderBy('id', 'asc')->get(['id', 'table_name', 'table_name_alias', 'updated_by', 'updated_at']);
        $response_data = [
            'count' => $tables->count(),
            'tables' => $tables->toArray()
        ];
        return WebApiResponse::success($response_data);
    }

    /**
     * Insert new record on 'm_tables' and create new table on DB
     *
     * @group Tables
     * @queryParam  table_name required string, Name of the table. Example: tableName
     * @queryParam  table_name_alias  required  string, Name of the table alias. Example: tableName_alias
     *
     * @param Request $request
     * @return Response
     *@responseFile apidocs/responses/tables/tables.insert.json
     *
     */

    public function add(Request $request)
    {
        try {
            $table = DB::transaction(function () use ($request) {
                $params = [
                    'table_name' => $request->table_name,
                    'table_name_alias' => $request->table_name_alias,
                ];
                return $this->DefinitionTableService->add($params);
            });

            $response_data = [
                'created' => true,
                'table'  => $table->toArray() // For Testing purpose
            ];
            return WebApiResponse::success($response_data);
        } catch (ValidationException $e) {
            return response()->json($e->failures(), 422);
        } catch (Exception $e) {
            return WebApiResponse::unexpectedError($e);
        }
    }

    /**
     * Update existing record on 'm_tables' and update table on DB
     *
     * @group Tables
     * @queryParam  id required int, Id of the Table that to be updated. Example: 1
     * @queryParam  table_name required string, Name of the table. Example: tableName
     * @queryParam  table_name_alias required string, Name of the table alias. Example: tableName_alias
     *
     * @param Request $request
     * @return Response
     * @responseFile apidocs/responses/tables/tables.update.json
     *
     */

    public function update(Request $request)
    {
        // RENAME TABLE tb1 TO tb2
        $tableObj = new Table();
        $table = $tableObj->findOrFail($request->id);
        $validator = Validator::make(
            $request->all(),
            [
                'table_name'        => ['required', 'string', 'max:64', Rule::unique('m_tables')->ignore($table->id)->whereNull('deleted_at')],
                'table_name_alias'  => 'required|string|max:255',
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->errors()->toArray(), 422);
        }
        DB::beginTransaction();
        try {
            $table->table_name       = $request->table_name;
            $table->table_name_alias = $request->table_name_alias;

            if ($table->isDirty('table_name')) {
                if (Schema::hasTable($request->table_name)) {
                    return response()->json(['table_name' => ['A Table already exists with this name. Please try different name']], 422);
                }
                Schema::rename($table->getOriginal('table_name'), $request->table_name);
            }


            $table->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage());
        }
        $response_data = [
            'updated' => true,
            'table'  => $table->toArray() // For Testing purpose
        ];
        return WebApiResponse::success($response_data);
    }

    /**
     * Confirm how relation the table has.
     *
     * @group Tables
     * @queryParam  id required int, Id of the record that to be deleted. Example: 1
     *
     * @param Request $request
     * @return Response
     * @response 200 {"message": "This table has following condition(s).\n・table columns are related to this table.\nAre you sure you want to delete this item?"}
     *
     */

    public function confirmRelation(Request $request)
    {
        $table = Table::findOrFail($request->id);
        $tableName = $table->table_name;

        if (Schema::hasTable($tableName)) {
            $rawDataCnt = DB::table($tableName)->count();
            if ($rawDataCnt > 0) {
                return response()->json(['error' => 'This table can not be deleted because the raw data table has data.']);
            }
        }

        $msg = "\"" . $tableName . "\" is related to following definitions."
            . "\nThese definitions are going to be deleted together.";

        $tableColumnsCnt = TableColumns::where('table_id', $request->id)->count();
        if ($tableColumnsCnt > 0) {
            $msg .= "\n・table columns";
        }

        $datasources = Datasource::where('table_id', $request->id);
        $datasourceCnt = $datasources->count();
        $datasourceColumnsCnt = 0;
        if ($datasourceCnt > 0) {
            $msg .= "\n・datasource";

            $hasDatasourceColumns = false;
            foreach ($datasources->get() as $datasource) {
                $msg .= "\n　　・" . $datasource->datasource_name;

                $datasourceColumnsCnt = DatasourceColumns::where('datasource_id', $datasource->id)->count();
                if ($datasourceColumnsCnt > 0) {
                    $hasDatasourceColumns = true;
                }
            }

            if ($hasDatasourceColumns) {
                $msg .= "\n・datasource columns";
            }
        }

        if ($tableColumnsCnt == 0 && $datasourceCnt == 0 && $datasourceColumnsCnt == 0) {
            $msg = '';
        } else {
            $msg .= "\nAre you sure you want to delete these definitions?";
        }

        return response()->json(['message' => $msg]);
    }

    /**
     * Delete existing record on 'm_tables' and 'm_table_columns' and delete table on DB
     *
     * @group Tables
     * @queryParam  id required int, Id of the record that to be deleted. Example: 1
     *
     * @param Request $request
     * @return Response
     * @response 200 {"success": "Deleted Successfully!"}
     *
     */

    public function delete(Request $request)
    {
        $table = Table::findOrFail($request->id);
        $tableColumns = TableColumns::where('table_id', $request->id);
        $tableName = $table->table_name;

        if (Schema::hasTable($tableName)) {
            $rawDataCnt = DB::table($tableName)->count();
            if ($rawDataCnt > 0) {
                return response()->json(['error' => 'This table can not be deleted because the raw data table has data.']);
            }
        }

        $datasources = Datasource::where('table_id', $request->id)->get();

        DB::beginTransaction();
        try {
            foreach ($datasources as $datasource) {
                DatasourceColumns::where('datasource_id', $datasource->id)->delete();
                Datasource::findOrFail($datasource->id)->delete();
            }
            $tableColumns->delete();
            $table->delete();
            Schema::dropIfExists($tableName);
            DB::commit();
            return response()->json(['success' => 'Deleted Successfully!']);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage());
        }
    }
}
