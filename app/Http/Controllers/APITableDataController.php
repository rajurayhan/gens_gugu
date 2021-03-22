<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Table;
use App\Libraries\Utility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Libraries\WebApiResponse;
use App\Services\TableDataSearchService;

/**
 * [API]Controller class for operating data tables
 *
 * @copyright GuGu All Rights Reserved
 */
class APITableDataController extends Controller
{
    protected $tableDataSearchService;

    const SUPPORTED_PARAMETER_GET_TABLE_DETAILS = ['page', 'itemsPerPage', 'sortBy', 'sortDesc', 'searchWords'];

    public function __construct(TableDataSearchService $tableDataSearchService)
    {
        $this->tableDataSearchService = $tableDataSearchService;
    }

    /**
     * Return a specific definition table information
     *
     * @group    Table Data
     * @urlParam id int, Table ID . Example: 1
     * @param    Request $request
     * @return   json WebAPIResponse::success or WebAPIResponse::error
     */
    public function getTableInfo(Request $request)
    {
        $tableObj = new Table();
        $table = $tableObj->findOrFail($request->id);
        $tableColumns = $this->tableDataSearchService->getTableHeader($request->id);

        $data = [
            'id' => $table->id,
            'table_name' => $table->table_name,
            'table_name_alias' => $table->table_name_alias,
            'columns' => $tableColumns
        ];
        return WebApiResponse::success($data);
    }

    /**
     * Get data from specified table with conditions
     *
     * @group      Table Data
     * @urlParam   id required int, Table ID.Example: 1
     * @queryParam page int, The limit records to get. Should be grater than 1. No-example
     * @queryParam itemsPerPage string, Sort column name. Use this parameter with sort_order together. No-example
     * @queryParam sortBy array, Sort column name. Use this parameter with sortDesc together. No-example
     * @queryParam sortDesc array, Sort order direction. 'asc' or 'desc'. Example: No-example
     * @queryParam searchWords array, Search for these words. Example: No-example
     *
     * @param  Request $request
     * @return Response
     */
    public function getTableData(Request $request)
    {

        //Validation
        $validator = Validator::make(
            $request->all(),
            [
                'page'          => 'numeric',
                'itemsPerPage'  => 'numeric',
                'sortBy'        => 'array',
                'sortBy.*'      => 'string',
                'sortDesc'      => 'array',
                'sortDesc.*'    => 'in:true,false',
                'searchWords'    => 'array',
                'searchWords.*'  => 'string'
            ]
        );
        if ($validator->fails()) {
            return WebApiResponse::parameterValidationError($validator);
        }

        // Check unsupported parameters
        $unsupported_params = Utility::getUnsupportParameters($request->all(), self::SUPPORTED_PARAMETER_GET_TABLE_DETAILS);
        if (count($unsupported_params) > 0) {
            return WebApiResponse::unsupportParameterError($unsupported_params);
        }

        //re-set parameters for service
        $params = [];
        if ($request->has('page')) {
            $params['page'] = $request->page;
        }
        if ($request->has('itemsPerPage') && $request->itemsPerPage > 0) {
            // because -1 is for all data, don't need to be set itemsPerPage
            $params['itemsPerPage'] = $request->itemsPerPage;
        }
        if ($request->has('sortBy') && count($request->sortBy) > 0) {
            // service allow only 1 column to sort
            $params['sortBy'] = $request->sortBy[0];
        }
        if ($request->has('sortDesc') && count($request->sortDesc) > 0) {
            // service allow only 1 column to sort
            $params['sortDesc'] = $request->sortDesc[0];
        }
        if ($request->has('searchWords') && count($request->searchWords) > 0) {
            $params['searchWords'] = $request->searchWords;
        }

        // call service
        $data = $this->tableDataSearchService->getTableData($request->id, $params);
        return WebApiResponse::success($data);
    }
}
