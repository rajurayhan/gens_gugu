<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\File;
use App\Libraries\Utility;
use Illuminate\Http\Request;
use App\Constant\ValidationRule;
use App\Libraries\WebApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Services\FileService;

class FileController extends Controller
{
    protected $FileService;

    const SUPPORTED_PARAMETER_GET = ['limit', 'sort_by', 'sort_order'];
    const SUPPORTED_PARAMETER_DELETE = [];

    public function __construct(
        FileService $FileService
    ) {
        $this->FileService = $FileService;
    }

    /**
     * Return uploaded file data list
     * DB登録済ファイル一覧をJSON形式で返す
     *
     * @group      Uploaded Files
     * @queryParam limit      int,    The limit records to get. Should be grater than 1. Example: 1
     * @queryParam sort_by    string, Sort column name. Use this parameter with sort_order together. Example: created_at
     * @queryParam sort_order string, Sort order direction. 'asc' or 'desc'. Example: desc
     *
     * @param  Request $request
     * @return Response
     */

    public function getFileList(Request $request)
    {

        // Check unsupported parameters
        $unsupported_params = Utility::getUnsupportParameters($request->all(), self::SUPPORTED_PARAMETER_GET);
        if (count($unsupported_params) > 0) {
            return WebApiResponse::unsupportParameterError($unsupported_params);
        }

        // Check parameter values
        $validator = Validator::make(
            $request->all(),
            [
            'limit' => ['integer', 'gte:1'],
            'sort_by' => array_merge(ValidationRule::COLUMN_NAME, ['required_with:sort_order']),
            'sort_order' => ValidationRule::SORT_ORDER
            ]
        );
        if ($validator->fails()) {
            return WebApiResponse::parameterValidationError($validator);
        }

        // Begin querying the model
        $query = File::query();
        if (isset($request->limit)) {
            $query = $query->take($request->limit);
        }
        if (isset($request->sort_by)) {
            if (isset($request->sort_order)) {
                $query = $query->orderBy($request->sort_by, $request->sort_order);
            } else {
                $query = $query->orderBy($request->sort_by, 'asc');
            }
        }

        // Execute the query
        $files = $query->get(['id', 'datasource_id', 'original_name', 'sheet_name', 'updated_at']);

        // Return response
        $response_data = [
            'count' => $files->count(),
            'files' => $files->toArray()
        ];
        return WebApiResponse::success($response_data);
    }

    /**
     * Delete uploaded file data
     * DB登録済ファイルを削除して、結果をJSON形式で返す
     *
     * @group    Uploaded Files
     * @urlParam file_id required The ID of the post
     *
     * @param  int $file_id
     * @return Response
     */

    public function deleteFile(Request $request, $file_id = null)
    {
        // Check unsupported parameters
        $unsupported_params = Utility::getUnsupportParameters($request->all(), self::SUPPORTED_PARAMETER_DELETE);
        if (count($unsupported_params) > 0) {
            return WebApiResponse::unsupportParameterError($unsupported_params);
        }

        // Check parameter values
        $validator = Validator::make(['file_id' => $file_id], ['file_id' => ['required', 'integer', 'gte:1']]);
        if ($validator->fails()) {
            // ユーザーがURIで指定したリソースがないため、Not found を返す
            return WebApiResponse::resourceNotFoundError(
                [
                trans('error_details_message.file_does_not_exist')
                ]
            );
        }

        if ($this->FileService->deleteRecordsFromRawTableAndFilesTable($file_id)) {
            // Success response
            $response_data = [];
            return WebApiResponse::success($response_data);
        } else {
            return WebApiResponse::resourceNotFoundError(
                [
                trans('error_details_message.file_does_not_exist')
                ]
            );
        }
    }
}
