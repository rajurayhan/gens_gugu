<?php

namespace App\Http\Controllers;

use App\Models\DatasourceColumns;
use App\Models\Datasource;
use App\Models\Table;
use App\Models\TableColumns;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DB;
use Validator;
use App\Services\DefinitionDatasourceColumnService;
use App\Exceptions\Exception;
use App\Exceptions\ValidationException;
use App\Libraries\WebApiResponse;

class DatasourceColumnsController extends Controller
{

    protected $DefinitionDatasourceColumnService;

    public function __construct(DefinitionDatasourceColumnService $DefinitionDatasourceColumnService)
    {
        $this->DefinitionDatasourceColumnService = $DefinitionDatasourceColumnService;
    }

    /**
     * Return a Data source columns list from 'm_datasource_columns'
     *
     * @group Data Source Columns
     * @queryParam  id int, The id of m_datasources to get sorted list by id. Should be grater than 0.Example: 1
     *
     * @param Request $request
     * @return Response
     */

    public function getDatasourceColumns(Request $request)
    {
        $dataSourceId = $request->id;
        if (isset($dataSourceId) && !empty($dataSourceId)) {
            $data_column_mapping = DatasourceColumns::where('datasource_id', $dataSourceId)->get();
        } else {
            $data_column_mapping = DatasourceColumns::all();
        }
        $DataColumnMapping = $data_column_mapping->map(function ($definition) {
            $definition->DataSourceName         = $definition->dataSource->datasource_name;
            $definition->DataSourceTableName    = $definition->dataSource->tables->table_name;
            $definition->ColumnName             = $definition->tableDefinition->column_name;
            $definition->unsetRelation('m_datasources');
            return $definition;
        });
        return response()->json($DataColumnMapping);
    }

    /**
     * Return table columns from 'm_table_columns'
     *
     * @group Data Source Columns
     * @urlParam  id int, The id of 'm_tables' to get sorted column by table_id. Should be grater than 0. Example: 1
     * @param $id
     * @return Response
     *
     */

    public function getTableColumns($id)
    {
        if ($id > 0) {
            $table_definition = TableColumns::where('table_id', $id)->get();
        } else {
            $table_definition = TableColumns::all();
        }
        return response()->json($table_definition);
    }

    /**
     * Get table_id form m_datasource
     *
     * @group Data Source Columns
     * @urlParam  id int, The id of 'm_datasources' to get table_id from m_datasource.Example: 1
     *
     * @param Request $request
     * @return Response
     */

    public function getTableIdOfDataSource($id)
    {
        $data_source = Datasource::findOrFail($id);
        return response()->json($data_source->table_id);
    }

    /**
     * Insert new record on 'm_datasource_columns'
     *
     * @group Data Source Columns
     * @queryParam  datasource_id required int, The id of m_datasource . Should be grater than 0. Example: 1
     * @queryParam  datasource_column_name required string, Name of the datasource column. Example: columnName
     * @queryParam  datasource_column_number required int, The datasource column number,should be less than 16384.Example: 2
     * @queryParam  table_column_id required int, The table_column_id,should be greater than 0.Example: 1
     *
     * @param  Request $request
     * @return Response
     * @responseFile apidocs/responses/datasourceColumn/datasourceColumn.insert.json
     *
     */

    public function add(Request $request)
    {
        try {
            $datasourceColumn = DB::transaction(function () use ($request) {
                $params = [
                    'datasource_id'             => $request->datasource_id,
                    'datasource_column_number'  => $request->datasource_column_number,
                    'datasource_column_name'    => $request->datasource_column_name,
                    'table_column_id'           => $request->table_column_id,
                ];
                return $this->DefinitionDatasourceColumnService->add($params);
            });

            $datasourceColumn->DataSourceName        = $datasourceColumn->dataSource->datasource_name;
            $datasourceColumn->DataSourceTableName   = $datasourceColumn->dataSource->tables->table_name;
            $datasourceColumn->ColumnName            = $datasourceColumn->tableDefinition->column_name;

            return WebApiResponse::success($datasourceColumn->toArray());
        } catch (ValidationException $e) {
            //TODO should use WebAPIResponse
            return response()->json($e->failures(), 422);
        } catch (Exception $e) {
            return WebApiResponse::unexpectedError($e);
        }
    }

    /**
     * Update an existing record from 'm_datasource_columns'
     *
     * @group Data Source Columns
     * @queryParam  id required int, The id of m_datasource_columns which to be updated. Should be grater than 0. Example: 1
     * @queryParam  datasource_column_name required string, Name of the datasource column. Example: columnName
     * @queryParam  datasource_column_number required int, The datasource column number,should be less than 16384.Example: 2
     * @queryParam  table_column_id required int, The id of 'm_table_columns',should be greater than 0.Example: 1
     *
     * @param  Request $request
     * @return Response
     * @responseFile apidocs/responses/datasourceColumn/datasourceColumn.update.json
     *
     */

    public function update(Request $request)
    {
        $data_column_mapping     = DatasourceColumns::findOrFail($request->id);
        $validator = Validator::make($request->all(), [
            'datasource_column_number'  => 'required|integer|digits_between:1,5|min:1|max:16384',
            'datasource_column_name'    => 'required|string|max:255',
            'table_column_id'           => [
                'required',
                'integer',
                'digits_between:1,11',
                'min:1',
                Rule::unique('m_datasource_columns')->ignore($data_column_mapping)->where('datasource_id', $data_column_mapping->datasource_id)->whereNull('deleted_at')
            ]
        ]);

        if (!$validator->fails()) {
            $data_column_mapping->datasource_column_number  =  $request->datasource_column_number;
            $data_column_mapping->datasource_column_name  =  $request->datasource_column_name;
            $data_column_mapping->table_column_id  =  $request->table_column_id;

            $data_column_mapping->save();

            $data_column_mapping->DataSourceName        = $data_column_mapping->dataSource->datasource_name;
            $data_column_mapping->DataSourceTableName   = $data_column_mapping->dataSource->tables->table_name;
            $data_column_mapping->ColumnName            = $data_column_mapping->tableDefinition->column_name;

            return response()->json($data_column_mapping);
        } else {
            return response()->json($validator->errors()->toArray(), 422);
        }
    }

    /**
     * Delete an existing record from 'm_datasource_columns'
     *
     *@group Data Source Columns
     *@queryParam  id required int, The id of m_datasource_columns which to be deleted. Should be grater than 0.
     * Example: 1
     *
     * @param  Request $request
     * @return Response
     * @response 200 {"success": "Deleted Successfully!"}
     *
     */

    public function delete(Request $request)
    {
        $data_column_mapping     = DatasourceColumns::findOrFail($request->id);
        $data_column_mapping->delete();
        return response()->json('Deleted Successfully!');
    }
}
