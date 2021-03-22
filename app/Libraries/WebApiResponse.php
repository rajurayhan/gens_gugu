<?php

namespace App\Libraries;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

class WebApiResponse
{
    /**
     * Success code of HTTP status code 200(Success)
     */
    const SUCCESS_CODE_NORMAL = 10;
    const SUCCESS_CODE_SEMI_NORMAL = 20;
    const SUCCESS_CODE_CONFIRMATION = 30;

    /**
     * Error code of HTTP status code 400(Bad parameter)
     */
    const ERROR_CODE_PARAMETER_ERROR = 10;
    const ERROR_CODE_UNSUPPORT_PARAMETER = 20;
    const ERROR_CODE_RESOURCE_NOT_FOUND = 30;

    /**
     * Error code of HTTP status code 500(Internal server error)
     */
    const ERROR_CODE_QUERY_EXCEPTION = 10;
    const ERROR_CODE_UNEXPECTED_ERROR = 99;

    /**
     * Query Exception発生時のエラーメッセージ
     * DB接続できない場合、DBからエラーメッセージを取得できないため、ここで定義しておく
     */
    const ERROR_MESSAGE_QUERY_EXCEPTION = '予期せぬシステムエラーが発生しました。システム管理者へお問い合わせください。';

    /**
     * WebAPIのエラーResponseを返す
     *
     * @param int $status_code HTTPステータスエラーコード
     * @param int $error_code エラーコード
     * @param string $error_message エラーメッセージ
     * @param array $error_details エラーの詳細
     * @return Response
     */
    private static function error(int $status_code, int $error_code, string $error_message, array $error_details)
    {
        $response_data = [
            'error_code' => $error_code,
            'error_message' => $error_message,
            'error_details_count' => count($error_details),
            'error_details'  => $error_details,
        ];
        Log::error('Status code: ' . $status_code);
        Log::error($response_data);
        return response()->json($response_data, $status_code, ['Content-Type' => 'text/json'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * WebAPIの成功Responseを返す
     *
     * @param array $response_data JSONデータで返すデータ配列
     * @return Response
     */
    public static function success(array $response_data)
    {
        $status_code = Response::HTTP_OK;
        // TODO ログ出力機能がマージされた後でLog:debugを削除する
        Log::debug('Status code: ' . $status_code);
        Log::debug($response_data);
        return response()->json($response_data, $status_code, ['Content-Type' => 'text/json'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * WebAPIの成功Responseを返す (準正常系)
     * Semi normal success response
     *
     * @param array $response_data JSONデータで返すデータ配列
     * @return Response
     */
    public static function successSemiNormal(string $message, array $response_data)
    {
        $data = [
            'code' => self::SUCCESS_CODE_SEMI_NORMAL,
            'message' => $message,
            'data'  => $response_data,
        ];

        return self::success($data);
    }

    /**
     * WebAPIの成功Responseを返す (確認)
     * Confirmation success response
     *
     * @param array $response_data JSONデータで返すデータ配列
     * @return Response
     */
    public static function successConfirmation(string $message, array $response_data)
    {
        $data = [
            'code' => self::SUCCESS_CODE_CONFIRMATION,
            'message' => $message,
            'data'  => $response_data,
        ];

        return self::success($data);
    }

    /**
     * パラメータバリデーションエラーのResponseを返す
     * $validatorにエラー情報がない場合、例外を投げる
     *
     * @param Validator $validator バリデーション結果
     * @return Response
     */
    public static function parameterValidationError(Validator $validator)
    {
        if (count($validator->errors()->all()) < 1) {
            Log::error(__FUNCTION__ . " is called without validation error.");
            throw new Exception();
        }
        return self::error(
            Response::HTTP_BAD_REQUEST,
            self::ERROR_CODE_PARAMETER_ERROR,
            trans('error_message_index.parameter_error'),
            $validator->errors()->all()
        );
    }

    /**
     * サポートしていないパラメータエラーのResponseを返す
     * $unsupported_paramsが空の配列の場合、例外を投げる
     *
     * @param array $unsupported_params サポートしていないパラメータの配列
     * @return Response
     */
    public static function unsupportParameterError(array $unsupported_params)
    {
        if (count($unsupported_params) < 1) {
            Log::error(__FUNCTION__ . " is called but there is an unsupported parameter.");
            throw new Exception();
        }
        return self::error(
            Response::HTTP_BAD_REQUEST,
            self::ERROR_CODE_UNSUPPORT_PARAMETER,
            trans('error_message_index.unsupported_parameter_error'),
            array_map(function ($value) {
                return $value . ':' . trans('error_details_message.unsupported_parameter_error');
            }, $unsupported_params)
        );
    }

    /**
     * サポートしていないパラメータ、特にアップロードされたExcel内の詳細エラーのResponseを返す
     *
     * @param array $error_details error_detailsに設定するメッセージの配列
     * @return Response
     */
    public static function validationErrorForDetails(array $error_details)
    {
        return self::error(
            Response::HTTP_BAD_REQUEST,
            self::ERROR_CODE_UNSUPPORT_PARAMETER,
            trans('error_message_index.validation_error'),
            $error_details
        );
    }

    /**
     * パラメータで指定されたデータがDB上になかった場合のエラーのResponseを返す
     *
     * @param array $error_details error_detailsに設定するメッセージの配列
     * @return Response
     */
    public static function resourceNotFoundError(array $error_details)
    {
        return self::error(
            Response::HTTP_NOT_FOUND,
            self::ERROR_CODE_RESOURCE_NOT_FOUND,
            trans('error_message_index.resource_not_found'),
            $error_details
        );
    }

    /**
     * query exceptionのResponseを返す
     *
     * @param QueryException $exception
     * @return Response
     */
    public static function queryError(QueryException $exception)
    {
        Log::error('Error message: ' . $exception->getMessage());
        Log::error('SQL: ' . $exception->getSql());
        Log::error('Exception code: ' . $exception->getCode());
        return self::error(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::ERROR_CODE_QUERY_EXCEPTION,
            self::ERROR_MESSAGE_QUERY_EXCEPTION,
            []
        );
    }

    /**
     * exceptionのResponseを返す
     *
     * @param Exception $exception
     * @return Response
     */
    public static function unexpectedError(Exception $exception)
    {
        Log::error('file:' . $exception->getFile());
        Log::error('line:' . $exception->getLine());
        Log::error('Error message: ' . $exception->getMessage());
        Log::error('Exception code: ' . $exception->getCode());
        return self::error(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            self::ERROR_CODE_UNEXPECTED_ERROR,
            trans('error_message_index.unexpected_system_error'),
            []
        );
    }
}
