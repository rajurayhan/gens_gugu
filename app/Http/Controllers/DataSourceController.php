<?php

namespace App\Http\Controllers;

use App\Libraries\Utility;
use App\Libraries\WebApiResponse;
use App\Models\Datasource;
use App\Models\DatasourceColumns;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DB;
use Validator;
use App\Exceptions\Exception;
use App\Services\DefinitionDatasourceService;
use App\Exceptions\ValidationException;

class DataSourceController extends Controller
{
    const GET_SUPPORTED_PARAMETER = [];

    protected $DefinitionDatasourceService;

    public function __construct(DefinitionDatasourceService $DefinitionDatasourceService)
    {
        $this->DefinitionDatasourceService = $DefinitionDatasourceService;
    }

    /**
     * Return a list of data sources
     *
     * @group Data Source

     * @response  400 {
     *    "error_code": 20,
     *    "error_message": "未対応のパラメータが設定されました。",
     *    "error_details_count": 2,
     *    "error_details": [
     *    "items per pageには、数字を指定してください。:このパラメータには対応していません。",
     *    "sort byは配列でなくてはなりません。:このパラメータには対応していません。"
     *    ]
     *}
     *
     * @param  Request $request
     * @return Response
     */

    public function getDataSourceList(Request $request)
    {
        // Check unsupported parameters
        $unsupported_params = Utility::getUnsupportParameters($request->all(), self::GET_SUPPORTED_PARAMETER);
        if (count($unsupported_params) > 0) {
            return WebApiResponse::unsupportParameterError($unsupported_params);
        }

        // Check parameter values
        // Do nothing
        // TODO Support for order by parameters(sort_by, sort_order)

        // Begin querying the model
        $query = Datasource::query();

        // Execute the query
        $datasources = $query->orderBy('id', 'asc')->get(['id', 'datasource_name', 'table_id', 'starting_row_number']);

        // Join table name alias
        $datasources_with_tablename_alias = $datasources->map(
            function ($definition) {
                if ($definition->tables) {
                    $definition->table_name = $definition->tables->table_name;
                } else {
                    $definition->table_name = '';
                }
                $definition->unsetRelation('tables');
                return $definition;
            }
        );

        // Return response
        $response_data = [
            'count' => $datasources_with_tablename_alias->count(),
            'datasources' => $datasources_with_tablename_alias->toArray()
        ];
        return WebApiResponse::success($response_data);
    }


    /**
     * Return all Data source from 'm_datasources'
     *
     * @group Data Source
     * @return Response
     * @responseFile apidocs/responses/datasource/datasource.get.json
     *
     */

    public function getDataSource()
    {
        $data_source = Datasource::all();

        $DataSource = $data_source->map(function ($definition) {
            if ($definition->tables) {
                $definition->tableName = $definition->tables->table_name;
            } else {
                $definition->tableName = '';
            }
            $definition->unsetRelation('m_tables');
            return $definition;
        });
        return response()->json($DataSource);
    }


    /**
     * Insert new record on 'm_datasources'
     *
     * @group Data Source
     * @queryParam  datasource_name required string, Name of the datasource. Example: sourceName
     * @queryParam  table_id  required int, The id of 'm_tables'.Should be grater than 0. Example: 1
     * @queryParam  starting_row_number  required int, Starting row number of datasource. Example: 2
     *
     * @param  Request $request
     * @return Response
     * @responseFile apidocs/responses/datasource/datasource.insert.json
     *
     */

    public function add(Request $request)
    {

        try {
            $datasource = DB::transaction(function () use ($request) {
                $params = [
                    'datasource_name' => $request->datasource_name,
                    'table_id' => $request->table_id,
                    'starting_row_number' => $request->starting_row_number,
                ];
                return $this->DefinitionDatasourceService->add($params);
            });

            // set table_name for easy access
            $datasource->tableName = $datasource->tables->table_name;

            return WebApiResponse::success($datasource->toArray());
        } catch (ValidationException $e) {
            return response()->json($e->failures(), 422);
        } catch (Exception $e) {
            return WebApiResponse::unexpectedError($e);
        }
    }


    /**
     * Update existing record from 'm_datasources'
     *
     * @group Data Source
     * @queryParam  id required int, The id of record which to be updated. Should be grater than 0. Example: 1
     * @queryParam  datasource_name   required  string, Name of the datasource. Example: sourceName
     * @queryParam  table_id  required int, The id of 'm_tables'.Should be grater than 0. Example: 1
     * @queryParam  starting_row_number  required  int, Starting row number of datasource. Example: 2
     *
     * @param  Request $request
     * @return Response
     * @responseFile apidocs/responses/datasource/datasource.update.json
     *
     */

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'datasource_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('m_datasources', 'datasource_name')->whereNull('deleted_at')->ignore($request->id),
            ],
            'table_id'              => 'required|integer|digits_between:1,11|min:1|exists:m_tables,id',
            'starting_row_number'   => 'required|integer|digits_between:1,7|min:1|max:1048576',
        ]);

        if (!$validator->fails()) {
            $data_source     = Datasource::findOrFail($request->id);

            $data_source->datasource_name       =  $request->datasource_name;
            $data_source->table_id              =  $request->table_id;
            $data_source->starting_row_number   =  $request->starting_row_number;

            $data_source->save();
            $data_source->tableName = $data_source->tables->table_name;

            return response()->json($data_source);
        } else {
            return response()->json($validator->errors()->toArray(), 422);
        }
    }

    /**
     * Delete an existing record from 'm_datasources' and 'm_datasource_columns'
     *
     * @group Data Source
     * @queryParam  id required int, Id of the record which to be deleted. Example: 1
     *
     * @param  Request $request
     * @return Response
     * @response 200 {"success": "Deleted Successfully!"}
     *
     */

    public function delete(Request $request)
    {
        DB::transaction(function () use ($request) {
            DatasourceColumns::where('datasource_id', $request->id)->delete();
            Datasource::findOrFail($request->id)->delete();
        });
        return response()->json('Deleted Successfully!');
    }
}
