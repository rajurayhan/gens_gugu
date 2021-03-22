<?php

namespace Tests\Unit\Libraries;

use Tests\TestCase;
use App\Libraries\WebApiResponse;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebApiResponseTest extends TestCase
{
    // データベースの初期化にトランザクションを使う
    use RefreshDatabase;

    /**
     * 各テストメソッドの実行前に呼ばれるメソッド
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan("db:seed");
    }

    /**
     * 引数のデータがsuccessレスポンスに設定されて返ることの確認
     * Confirmation that the argument data is set to the success response and returns
     *
     * @return void
     */
    public function testSuccessResponse()
    {
        // Generate parameters
        $response_data = [
            'count' => 2,
            'files' => [
                'test.xlsx',
                'test2.xlsx',
            ],
        ];

        // Execute test target
        $response = WebApiResponse::success($response_data);

        // Check result
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($response_data, $response->original);
    }

    /**
     * 引数のデータが空配列の場合にもsuccessレスポンスが返ることの確認
     * Confirmation that the success response is returned even when the data in the argument is an empty array
     *
     * @return void
     */
    public function testSuccessResponseWithEmptyArray()
    {
        // Generate parameters
        $response_data = [];

        // Execute test target
        $response = WebApiResponse::success($response_data);

        // Check result
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($response_data, $response->original);
    }

    /**
     * 準正常のレスポンスが返ることの確認
     * Check the Semi-Normal Success Response is set to return argument data.
     *
     * @return void
     */
    public function testSemiNormalSuccessResponse()
    {
        // Generate parameters
        $response_msg = 'テストメッセージです';
        $response_data = [
            'count' => 2,
            'files' => [
                'test.xlsx',
                'test2.xlsx',
            ],
        ];

        // Execute test target
        $response = WebApiResponse::successSemiNormal($response_msg, $response_data);

        // Check result
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'code' => 20,
            'message' => $response_msg,
            'data' => $response_data,
        ], $response->original);
    }

    /**
     * 確認のレスポンスが返ることの確認
     * Check the Confirmation Success Response is set to return argument data.
     *
     * @return void
     */
    public function testConfirmationSuccessResponse()
    {
        // Generate parameters
        $confirm_msg = 'テストメッセージです';
        $response_data = [
            'count' => 2,
            'files' => [
                'test.xlsx',
                'test2.xlsx',
            ],
        ];

        // Execute test target
        $response = WebApiResponse::successConfirmation($confirm_msg, $response_data);

        // Check result
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'code' => 30,
            'message' => $confirm_msg,
            'data' => $response_data,
        ], $response->original);
    }

    /**
     * パラメータエラーのResponseに引数のデータが設定されて返ることの確認
     * Check that the parameter error Response is set to return argument data.
     *
     * @return void
     */
    public function testParameterValidationError()
    {
        // Generate parameters
        $parameter = ['key' => 'value'];
        $validation_rule = ['key' => 'integer', 'key2' => "required"];
        $validator = Validator::make($parameter, $validation_rule);

        // Execute test target
        $response = WebApiResponse::parameterValidationError($validator);

        // Check result
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            "error_code" => 10,
            "error_message" => "パラメータエラーが発生しました。",
            "error_details_count" => 2,
            "error_details" => [
                "keyは整数で指定してください。",
                "key2は必ず指定してください。",
            ],
        ], $response->original);
    }

    /**
     * バリデーションエラーが発生していないにもかかわらず呼ばれた場合には例外を投げることを確認する
     * Make sure to throw an exception if a validation error has not occurred but has been called
     *
     * @return void
     */
    public function testParameterValidationErrorWithoutValidationError()
    {
        // Set expected exception
        $this->expectException(Exception::class);

        // Generate parameters
        $parameter = ['key' => 'value'];
        $validation_rule = ['key' => 'required'];
        $validator = Validator::make($parameter, $validation_rule);

        // Execute test target
        $response = WebApiResponse::parameterValidationError($validator);

        // Expect exception
    }

    /**
     * サポートしていないパラメータエラーのResponseが正しく返ることの確認
     * Checking that the Response for unsupported parameter errors returns correctly
     *
     * @return void
     */
    public function testUnsupportedParameterError()
    {
        // Generate parameters
        $unsupported_params = ['param1', 'param2'];

        // Execute test target
        $response = WebApiResponse::unsupportParameterError($unsupported_params);

        // Check result
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            "error_code" => 20,
            "error_message" => "未対応のパラメータが設定されました。",
            "error_details_count" => 2,
            "error_details" => [
                'param1:このパラメータには対応していません。',
                'param2:このパラメータには対応していません。',
            ],
        ], $response->original);
    }

    /**
     * パラメータが空配列の場合には例外を投げることを確認する
     * Make sure to throw an exception if the parameter is an empty array
     *
     * @return void
     */
    public function testunsupportParameterErrorWithEmptyParameter()
    {
        // Set expected exception
        $this->expectException(Exception::class);

        // Generate parameters
        $unsupported_params = [];

        // Execute test target
        $response = WebApiResponse::unsupportParameterError($unsupported_params);

        // Expect exception
    }

    /**
     * パラメータの値がDB上にない場合のエラーResponseが正しく返ることの確認
     * Ensuring that the error response is returned correctly when the parameter value is not in the DB.
     *
     * @return void
     */
    public function testResourceNotFoundError()
    {
        // Generate parameters
        $error_details = [
            '指定されたファイルは存在しません。'
        ];

        // Execute test target
        $response = WebApiResponse::resourceNotFoundError($error_details);

        // Check result
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals([
            "error_code" => 30,
            "error_message" => "指定されたリソースは見つかりませんでした。",
            "error_details_count" => 1,
            "error_details" => [
                '指定されたファイルは存在しません。'
            ],
        ], $response->original);
    }

    /**
     * パラメータの値がDB上にない場合のエラーResponseが正しく返ることの確認
     * Ensuring that the error response is returned correctly when the parameter value is not in the DB.
     *
     * @return void
     */
    public function testResourceNotFoundErrorWithEmptyParameter()
    {
        // Generate parameters
        $error_details = [];

        // Execute test target
        $response = WebApiResponse::resourceNotFoundError($error_details);

        // Check result
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals([
            "error_code" => 30,
            "error_message" => "指定されたリソースは見つかりませんでした。",
            "error_details_count" => 0,
            "error_details" => [],
        ], $response->original);
    }

    /**
     * SQLエラーのResponseが正しく返ることの確認
     * Check that SQL error responses are returned correctly.
     *
     * @return void
     */
    public function testQueryError()
    {
        // Generate parameters
        $queryException = new QueryException('Test SQL', [], new Exception());

        // Execute test target
        $response = WebApiResponse::queryError($queryException);

        // Check result
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals([
            "error_code" => 10,
            "error_message" => "予期せぬシステムエラーが発生しました。システム管理者へお問い合わせください。",
            "error_details_count" => 0,
            "error_details" => [],
        ], $response->original);
    }

    /**
     * 予期せぬシステムエラーのResponseが正しく返ることの確認
     * Checking that the response to an unexpected system error is returned correctly
     *
     * @return void
     */
    public function testUnexpectedError()
    {
        // Generate parameters
        $exception = new Exception();

        // Execute test target
        $response = WebApiResponse::unexpectedError($exception);

        // Check result
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals([
            "error_code" => 99,
            "error_message" => "予期せぬシステムエラーが発生しました。システム管理者へお問い合わせください。",
            "error_details_count" => 0,
            "error_details" => [],
        ], $response->original);
    }
}
