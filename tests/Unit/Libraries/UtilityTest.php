<?php

namespace Tests\Unit\Libraries;

use Exception;
use App\Libraries\Utility;
use PHPUnit\Framework\TestCase;

class UtilityTest extends TestCase
{

    /**
     * 全てのリクエストパラメータが想定されているパラメータである場合、空の配列を返すことを確認する
     * Make sure to return an empty array if all the requested parameters are the expected parameters
     *
     * @return void
     */
    public function testUnsupportParametersWithAllSupportedParameters()
    {
        // Generate parameters
        $input_parameters = ['param1' => 1, 'param2' => 'test'];
        $supported_parameters = ['param1', 'param2'];

        // Execute test target
        $unsupported_params = Utility::getUnsupportParameters($input_parameters, $supported_parameters);

        // Check result
        $this->assertEquals([], $unsupported_params);
    }

    /**
     * リクエストパラメータに想定していないパラメータがある場合、そのパラメータの配列を返すことを確認する
     * If a request parameter has some unanticipated parameters, make sure that an array of the parameter is returned
     *
     * @return void
     */
    public function testUnsupportParameters()
    {
        // Generate parameters
        $input_parameters = ['param1' => 1, 'param2' => 'test', 'param3' => 3, 'param4' => 4];
        $supported_parameters = ['param2', 'param3'];

        // Execute test target
        $unsupported_params = Utility::getUnsupportParameters($input_parameters, $supported_parameters);

        // Check result
        $this->assertEquals(['param1', 'param4'], $unsupported_params);
    }

    /**
     * リクエストパラメータが空の場合、空の配列を返すことを確認する
     * If the request parameter is empty, make sure to return an empty array
     *
     * @return void
     */
    public function testUnsupportParametersWithEmptyParameter()
    {
        // Generate parameters
        $input_parameters = [];
        $supported_parameters = ['param1', 'param2'];

        // Execute test target
        $unsupported_params = Utility::getUnsupportParameters($input_parameters, $supported_parameters);

        // Check result
        $this->assertEquals([], $unsupported_params);
    }

    /**
     * 想定しているパラメータがない場合、リクエストパラメータのカラム名をすべて配列で返すことを確認する
     * If no parameter is expected, make sure that all the column names of the request parameter are returned as an array
     *
     * @return void
     */
    public function testUnsupportParametersWithEmptySuppoertedParameter()
    {
        // Generate parameters
        $input_parameters = ['param1' => 1, 'param2' => 'test'];
        $supported_parameters = [];

        // Execute test target
        $unsupported_params = Utility::getUnsupportParameters($input_parameters, $supported_parameters);

        // Check result
        $this->assertEquals(['param1', 'param2'], $unsupported_params);
    }

    /**
     * リクエストパラメータが多次元配列の場合でも、パラメータを返すことを確認する
     * Make sure that an array of some unanticipated parameters is returned, even if it is a multi-dimensional array
     *
     * @return void
     */
    public function testUnsupportParametersWithNestedArray()
    {
        // Generate parameters
        $input_parameters = [
            'param1' => [
                'param1_1' => 1,
                'param1-2' => 2,
            ],
            'param2' => [
                'param2_1' => 21,
                'param2_2' => 22,
            ],
            'param5' => [
                'param5_1' => 51,
                'param5_2' => 52,
            ]];
        $supported_parameters = ['param1', 'param2'];

        // Execute test target
        $unsupported_params = Utility::getUnsupportParameters($input_parameters, $supported_parameters);

        // Check result
        $this->assertEquals(['param5'], $unsupported_params);
    }
    
    /**
     * リクエストパラメータが添字配列の場合は例外を投げる
     * Throws an exception if the request parameter is an indexed array
     *
     * @return void
     */
    public function testUnsupportParametersWithSubscriptArray()
    {
        // Set expected exception
        $this->expectException(Exception::class);
        
        // Generate parameters
        $input_parameters = ['param1', 'param2', 'param5'];
        $supported_parameters = ['param1', 'param2'];

        // Execute test target
        $unsupported_params = Utility::getUnsupportParameters($input_parameters, $supported_parameters);

        // Expect exception
    }

    //Normal case 1
    public function testAlphaToNumber1()
    {
        // Input text parameters
        $input_alphabet = "y";
        $expected_result = 25;

        // Execute test target
        $Response = Utility::alpha2num($input_alphabet);

        // Check result
        $this->assertEquals($expected_result, $Response);
    }
    //Normal case 2
    public function testAlphaToNumber2()
    {
        // Input text parameters
        $input_alphabet = "BA";
        $expected_result = 53;

        // Execute test target
        $Response = Utility::alpha2num($input_alphabet);

        // Check result
        $this->assertEquals($expected_result, $Response);
    }
    // Check for empty data
    public function testEmptyDataAlphaToNumber()
    {
        // Input text parameters
        $input_alphabet ="";
        $expected_result = -96;

        // Execute test target
        $Response = Utility::alpha2num($input_alphabet);

        // Check result
        $this->assertEquals($expected_result, $Response);
    }

    // Check for unsupported data
    public function testUnsupportedDataAlphaToNumber()
    {
        // Input text parameters
        $input_alphabet = "A2";
        $expected_result = -20;

        // Execute test target
        $Response = Utility::alpha2num($input_alphabet);

        // Check result
        $this->assertEquals($expected_result, $Response);
    }

    //Normal case
    public function testNumberToAlpha()
    {
        // Input text parameters
        $input_number = 5;
        $expected_result = "E";

        // Execute test target
        $Response = Utility::num2alpha($input_number);

        // Check result
        $this->assertEquals($expected_result, $Response);
    }

    //Check for negative value
    public function testNumberToAlphaForNegativeNumber()
    {
        // Input text parameters
        $input_number = -25;
        $expected_result = "A";

        // Execute test target
        $Response = Utility::num2alpha($input_number);

        // Check result
        $this->assertEquals($expected_result, $Response);
    }
}
