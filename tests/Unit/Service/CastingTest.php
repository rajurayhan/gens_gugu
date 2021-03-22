<?php

namespace Tests\Unit;

use Exception;
use Tests\TestCase;
use App\Models\TableColumns;
use App\Services\CastingImportData;
use Illuminate\Support\Facades\Log;

class CastingTest extends TestCase
{
    private $casting_import_data;

    /**
     * 各テストメソッドの実行前に呼ばれるメソッド
     */
    protected function setUp(): void
    {
        parent::setUp();

        //テスト前処理
        $this->casting_import_data = resolve(CastingImportData::class);
    }
    
    // Char test
    /**
     * Charへのキャスト
     * 文字数パラメータが0より大きく、対象データが文字数パラメータより小さい場合
     *
     * @return void
     */
    public function testChar_ParamLengthGreaterThan0_DataLengthLessThanLengthParam()
    {
        $target_value = 'abcd';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = 5;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('abcd', $result);
    }
    
    /**
     * Charへのキャスト
     * 文字数パラメータが0より大きく、対象データが文字数パラメータに等しい場合
     *
     * @return void
     */
    public function testChar_ParamLengthGreaterThan0_DataLengthEqualLengthParam()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = 5;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('abcde', $result);
    }
    
    /**
     * Charへのキャスト
     * 文字数パラメータが0より大きく、対象データが文字数パラメータより大きい場合
     *
     * @return void
     */
    public function testChar_ParamLengthGreaterThan0_DataLengthGreaterThanLengthParam()
    {
        $target_value = 'abcdef';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = 5;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('abcde', $result);
    }
    
    /**
     * Charへのキャスト
     * 文字数パラメータが0より大きく、対象データがnullの場合
     *
     * @return void
     */
    public function testChar_ParamLengthGreaterThan0_DataIsNull()
    {
        $target_value = null;
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = 5;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Charへのキャスト
     * 文字数パラメータに0が入っている場合
     *
     * @return void
     */
    public function testChar_ParamLengthEqual0()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = 0;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('', $result);
    }
    
    /**
     * Charへのキャスト
     * 文字数パラメータに負の値が入っている場合
     *
     * @return void
     */
    public function testChar_ParamLengthLessThan0()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = -1;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Charへのキャスト
     * 文字数パラメータに小数の値が入っている場合
     *
     * @return void
     */
    public function testChar_ParamLengthIsDecimal()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = 1.1;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Charへのキャスト
     * 文字数パラメータに文字が入っている場合
     *
     * @return void
     */
    public function testChar_ParamLengthIsString()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = 'a';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Charへのキャスト
     * 文字数パラメータにnullが入っている場合
     *
     * @return void
     */
    public function testChar_ParamLengthIsNull()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'char';
        $table_columns->length = null;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    // Varchar test
    /**
     * Varcharへのキャスト
     * 文字数パラメータが0より大きく、対象データが文字数パラメータより小さい場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthGreaterThan0_DataLengthLessThanLengthParam()
    {
        $target_value = 'abcd';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = 5;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('abcd', $result);
    }
    
    /**
     * Varcharへのキャスト
     * 文字数パラメータが0より大きく、対象データが文字数パラメータに等しい場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthGreaterThan0_DataLengthEqualLengthParam()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = 5;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('abcde', $result);
    }
    
    /**
     * Varcharへのキャスト
     * 文字数パラメータが0より大きく、対象データが文字数パラメータより大きい場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthGreaterThan0_DataLengthGreaterThanLengthParam()
    {
        $target_value = 'abcdef';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = 5;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('abcde', $result);
    }
    
    /**
     * Varcharへのキャスト
     * 文字数パラメータが0より大きく、対象データがnullの場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthGreaterThan0_DataIsNull()
    {
        $target_value = null;
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = 5;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Varcharへのキャスト
     * 文字数パラメータに0が入っている場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthEqual0()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = 0;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('', $result);
    }
    
    /**
     * Varcharへのキャスト
     * 文字数パラメータに負の値が入っている場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthLessThan0()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = -1;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Varcharへのキャスト
     * 文字数パラメータに小数の値が入っている場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthIsDecimal()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = 1.1;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Varcharへのキャスト
     * 文字数パラメータに文字が入っている場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthIsString()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = 'a';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Varcharへのキャスト
     * 文字数パラメータにnullが入っている場合
     *
     * @return void
     */
    public function testVarchar_ParamLengthIsNull()
    {
        $target_value = 'abcde';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'varchar';
        $table_columns->length = null;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }

    //Tinyint test
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 0より小さい場合
     *
     * @return void
     */
    public function testTinyint_DataIsNotDecimal_DataLessThan0()
    {
        $target_value = '-1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-1, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 0と等しい場合
     *
     * @return void
     */
    public function testTinyint_DataIsNotDecimal_DataEqual0()
    {
        $target_value = '0';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 最大値127と等しい場合
     *
     * @return void
     */
    public function testTinyint_DataIsNotDecimal_DataEqual127()
    {
        $target_value = '127';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(127, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 最大値127より大きい場合
     *
     * @return void
     */
    public function testTinyint_DataIsNotDecimal_DataGreaterThan127()
    {
        $target_value = '128';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 最小値-128と等しい場合
     *
     * @return void
     */
    public function testTinyint_DataIsNotDecimal_DataEqualMinus128()
    {
        $target_value = '-128';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-128, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 最小値-128より小さい場合
     *
     * @return void
     */
    public function testTinyint_DataIsNotDecimal_DataLessThanMinus128()
    {
        $target_value = '-129';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }


    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含む数字、対象データが 0より小さい場合
     *
     * @return void
     */
    public function testTinyint_DataIsDecimal_DataLessThan0()
    {
        $target_value = '-0.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含む数字、対象データが 0と等しい場合
     *
     * @return void
     */
    public function testTinyint_DataIsDecimal_DataEqual0()
    {
        $target_value = '0.0';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含む数字、対象データが 整数部が最大値127と等しい場合
     *
     * @return void
     */
    public function testTinyint_DataIsDecimal_DataEqual255()
    {
        $target_value = '127.9';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(127, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含む数字、対象データが 整数部が最大値127より大きい場合
     *
     * @return void
     */
    public function testTinyint_DataIsDecimal_DataGreaterThan255()
    {
        $target_value = '128.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データが小数部を含む数字、対象データの有効桁数が16以上の場合
     *
     * @return void
     */
    public function testTinyint_DataIsDecimal_NumOfSignificantDigitsOfDataGreaterThanEqual16()
    {
        $target_value = '123.9999999999999';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(123, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データがnullの場合
     *
     * @return void
     */
    public function testTinyint_DataIsNull()
    {
        $target_value = null;
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Tinyintへのキャスト
     * 対象データに数字以外の文字が含まれる場合
     *
     * @return void
     */
    public function testTinyint_DataIsNotNumeric()
    {
        $target_value = '12a';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'tinyint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    //Int test
    /**
     * Intへのキャスト
     * 対象データが小数部を含まない数字、対象データが 0より小さい場合
     *
     * @return void
     */
    public function testInt_DataIsNotDecimal_DataLessThan0()
    {
        $target_value = '-1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-1, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含まない数字、対象データが 0と等しい場合
     *
     * @return void
     */
    public function testInt_DataIsNotDecimal_DataEqual0()
    {
        $target_value = '0';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含まない数字、対象データが最大値2147483647と等しい場合
     *
     * @return void
     */
    public function testInt_DataIsNotDecimal_DataEqual2147483647()
    {
        $target_value = '2147483647';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(2147483647, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含まない数字、対象データが最大値2147483647より大きい場合
     *
     * @return void
     */
    public function testInt_DataIsNotDecimal_DataGreaterThan2147483647()
    {
        $target_value = '2147483648';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含まない数字、対象データが最小値-2147483648と等しい場合
     *
     * @return void
     */
    public function testInt_DataIsNotDecimal_DataEqualMinus2147483648()
    {
        $target_value = '-2147483648';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-2147483648, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含まない数字、対象データが最小値-2147483648より小さい場合
     *
     * @return void
     */
    public function testInt_DataIsNotDecimal_DataLessThanMinus2147483648()
    {
        $target_value = '-2147483649';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Intへのキャスト
     * 対象データが小数部を含む数字、対象データが 0より小さい場合
     *
     * @return void
     */
    public function testInt_DataIsDecimal_DataLessThan0()
    {
        $target_value = '-0.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含む数字、対象データが 0と等しい場合
     *
     * @return void
     */
    public function testInt_DataIsDecimal_DataEqual0()
    {
        $target_value = '0.0';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含む数字、対象データが 整数部が最大値2147483647と等しい場合
     *
     * @return void
     */
    public function testInt_DataIsDecimal_DataEqual2147483647()
    {
        $target_value = '2147483647.9';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(2147483647, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含む数字、対象データが 最大値2147483647より大きい場合
     *
     * @return void
     */
    public function testInt_DataIsDecimal_DataGreaterThan4294967295()
    {
        $target_value = '2147483648.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含む数字、対象データが 整数部が最小値-2147483648と等しい場合
     *
     * @return void
     */
    public function testInt_DataIsDecimal_DataEqualMinus2147483648()
    {
        $target_value = '-2147483648.9';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-2147483648, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含む数字、対象データが 最小値-2147483648より小さい場合
     *
     * @return void
     */
    public function testInt_DataIsDecimal_DataLessThanMinus21474MinusMinus()
    {
        $target_value = '-2147483649.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データが小数部を含む数字、対象データの有効桁数が16以上の場合
     *
     * @return void
     */
    public function testInt_DataIsDecimal_NumOfSignificantDigitsOfDataGreaterThanEqual16()
    {
        $target_value = '123.9999999999999';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(123, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データがnullの場合
     *
     * @return void
     */
    public function testInt_DataIsNull()
    {
        $target_value = null;
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Intへのキャスト
     * 対象データに数字以外の文字が含まれる場合
     *
     * @return void
     */
    public function testInt_DataIsNotNumeric()
    {
        $target_value = '12a';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'int';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    //Bigint test
    /**
     * Bigintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 0より小さい場合
     *
     * @return void
     */
    public function testBigint_DataIsNotDecimal_DataLessThan0()
    {
        $target_value = '-1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-1, $result);
    }

    /**
     * Bigintへのキャスト
     * 対象データが小数部を含まない数字、対象データが -9223372036854775808と等しい場合
     *
     * @return void
     */
    public function testBigint_DataIsNotDecimal_DataMin()
    {
        $target_value = '-9223372036854775808';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(PHP_INT_MIN, $result);
    }

    /**
     * Bigintへのキャスト
     * 対象データが小数部を含まない数字、対象データが -9223372036854775808より小さい場合
     *
     * @return void
     */
    public function testBigint_DataIsNotDecimal_DataLessThanMin()
    {
        $target_value = '-9223372036854775809';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Bigintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 0と等しい場合
     *
     * @return void
     */
    public function testBigint_DataIsNotDecimal_DataEqual0()
    {
        $target_value = '0';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Bigintへのキャスト
     * 対象データが小数部を含まない数字、かつ、対象データがPHP_INT_MAXと等しい場合、対象データがint型で返る
     * MysqlのBigintの最大値は18446744073709551615より、intの最大値(PHP_INT_MAX)が小さいため、
     * 格納可能な値の最大値はPHP_INT_MAXとなる
     *
     * GENSで扱う数値の最大値はPHP_INT_MAXとする
     *
     * @return void
     */
    public function testBigint_DataIsNotDecimal_And_DataEqualPHPINTMAX_ReturnIntVal()
    {
        $target_value = '9223372036854775807';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(PHP_INT_MAX, $result);
    }
    
    /**
     * Bigintへのキャスト
     * 対象データが小数部を含まない数字、対象データが 18446744073709551615より大きい場合
     *
     * @return void
     */
    public function testBigint_DataIsNotDecimal_DataGreaterThan18446744073709551615()
    {
        $target_value = '18446744073709551616';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Bigintへのキャスト
     * 対象データが小数部を含む数字、対象データが 0より小さい場合
     *
     * @return void
     */
    public function testBigint_DataIsDecimal_DataLessThan0()
    {
        $target_value = '-0.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Bigintへのキャスト
     * 対象データが小数部を含む数字、対象データが 0と等しい場合
     *
     * @return void
     */
    public function testBigint_DataIsDecimal_DataEqual0()
    {
        $target_value = '0.0';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0, $result);
    }
    
    /**
     * Bigintへのキャスト
     * 対象データが小数部を含む数字、かつ対象データが整数部がPHP_INT_MAXと等しい場合、対象データの小数部を切り捨てた値がint型で返る
     * MysqlのBigintの最大値は18446744073709551615より、intの最大値(PHP_INT_MAX)が小さいため、
     * 格納可能な値の最大値はPHP_INT_MAXとなる。
     *
     * GENSで扱う数値の最大値はPHP_INT_MAXとする。
     * 対象データが小数の場合は小数部を切り捨てる。
     *
     * @return void
     */
    public function testBigint_DataIsDecimal_And_DataEqualPHPINTMAX_ReturnIntVal()
    {
        $target_value = '9223372036854775807.9';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(PHP_INT_MAX, $result);
    }
    
    /**
     * Bigintへのキャスト
     * 対象データが小数部を含む数字、対象データが 整数部が18446744073709551615より大きい場合
     *
     * @return void
     */
    public function testBigint_DataIsDecimal_DataGreaterThan18446744073709551615()
    {
        $target_value = '18446744073709551616.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * BigIntへのキャスト
     * 対象データが小数部を含む数字、対象データの有効桁数が16以上の場合
     *
     * @return void
     */
    public function testBigInt_DataIsDecimal_NumOfSignificantDigitsOfDataGreaterThanEqual16()
    {
        $target_value = '123.9999999999999';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(123, $result);
    }
    
    /**
     * BigIntへのキャスト
     * 対象データがnullの場合
     *
     * @return void
     */
    public function testBigInt_DataIsNull()
    {
        $target_value = null;
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Bigintへのキャスト
     * 対象データに数字以外の文字が含まれる場合
     *
     * @return void
     */
    public function testBigint_DataIsNotNumeric()
    {
        $target_value = '12a';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'bigint';

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    //decimal test
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含まない数字
     * ・ 桁数が 「全体の桁数」と等しい
     * ・ 正の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataIsNotDecimal_DataDigitNumEqualNumParam()
    {
        $target_value = '12345678';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(floatval(12345678), $result);
    }

    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含まない数字
     * ・ 桁数が 「全体の桁数」と等しい
     * ・ 負の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataIsNotDecimal_DataDigitNumEqualNumParamAndMinus()
    {
        $target_value = '-12345678';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(floatval(-12345678), $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含まない数字
     * ・ 桁数が 「全体の桁数」より大きい
     * ・ 正の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataIsNotDecimal_DataDigitNumGreaterThanNumParam()
    {
        $target_value = '123456789';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含まない数字
     * ・ 桁数が 「全体の桁数」より大きい
     * ・ 負の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataIsNotDecimal_DataDigitNumGreaterThanNumParamAndMinus()
    {
        $target_value = '-123456789';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より小さい
     * ・ 小数部の桁数が 「小数部の桁数」と等しい
     * ・ 正の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumLessThanParam_DataDigitDecimalEqualParam()
    {
        $target_value = '123456.78';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(123456.78, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より小さい
     * ・ 小数部の桁数が 「小数部の桁数」と等しい
     * ・ 負の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumLessThanParam_DataDigitDecimalEqualParamAndMinus()
    {
        $target_value = '-123456.78';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-123456.78, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より小さい
     * ・ 小数部の桁数が 「小数部の桁数」と等しい
     * ・ 正の数(0より大きいかつ1未満)
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumLessThanParam_DataDigitDecimalEqualParamAndLessThanOne()
    {
        $target_value = '0.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(0.1, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より小さい
     * ・ 小数部の桁数が 「小数部の桁数」と等しい
     * ・ 正の数(-1より大きいかつ0未満)
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumLessThanParam_DataDigitDecimalEqualParamAndMoreThanMinusOne()
    {
        $target_value = '-0.1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-0.1, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より小さい
     * ・ 小数部の桁数が 「小数部の桁数」より大きい
     * ・ 正の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumLessThanParam_DataDigitDecimalGreaterThanParam()
    {
        $target_value = '12345.678';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(12345.67, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より小さい
     * ・ 小数部の桁数が 「小数部の桁数」より大きい
     * ・ 負の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumLessThanParam_DataDigitDecimalGreaterThanParamAndMinus()
    {
        $target_value = '-12345.678';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(-12345.67, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より大きい
     * ・ 小数部の桁数が 「小数部の桁数」より小さい
     * ・ 正の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumGreaterThanParam_DataDigitDecimalLessThanParam()
    {
        $target_value = '12345678.9';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より大きい
     * ・ 小数部の桁数が 「小数部の桁数」より小さい
     * ・ 負の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumGreaterThanParam_DataDigitDecimalLessThanParamAndMinus()
    {
        $target_value = '-12345678.9';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より大きい
     * ・ 小数部の桁数が 「小数部の桁数」より大きい
     * ・ 正の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumGreaterThanParam_DataDigitDecimalGreaterThanParam()
    {
        $target_value = '12345678.901';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 設定値が以下のすべての条件を満たす。
     * ・「全体の桁数」が0より大きい、かつ66未満の整数
     * ・「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「対象データ」が以下のすべての条件を満たす。
     * ・ 小数部を含む
     * ・ 整数部と小数部合わせた桁数が 「全体の桁数」より大きい
     * ・ 小数部の桁数が 「小数部の桁数」より大きい
     * ・ 負の数
     *
     * @return void
     */
    public function testDecimal_AllParamValid_DataDigitNumGreaterThanParam_DataDigitDecimalGreaterThanParamAndMinus()
    {
        $target_value = '-12345678.901';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 「対象データ」がnullの場合
     *
     * @return void
     */
    public function testDecimal_DataIsNull()
    {
        $target_value = null;
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 2;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Decimalへのキャスト
     * 「小数部の桁数」が0以上、かつ31未満の整数
     * 「全体の桁数」に0が入っている
     *
     * @return void
     */
    public function testDecimal_MaxNumParamEqual0()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 0;
        $table_columns->decimal_part = 2;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「小数部の桁数」が0以上、かつ「全体の桁数」未満、かつ31未満の整数
     * かつ「全体の桁数」に19(PHP_INT_MAXの桁数)が入っている
     * かつ、対象データがPHP_INT_MAXと等しい場合、対象データがfloat型で返る
     *
     * GENSで扱う数値の最大値はPHP_INT_MAXとする
     *
     * @return void
     */
    public function testDecimal_DataEqualPHPINTMAX_ReturnIntVal()
    {
        Log::info(__FUNCTION__);

        $target_value = strval(PHP_INT_MAX);
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 19;
        $table_columns->decimal_part = 1;
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(floatval(PHP_INT_MAX), $result);
    }
    
    /**
     * Decimalへのキャスト
     * 「小数部の桁数」が0以上、かつ「全体の桁数」未満、かつ31未満の整数
     * 「全体の桁数」に0が入っている
     *
     * @return void
     */
    public function testDecimal_MaxNumEqualThan0()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 0;
        $table_columns->decimal_part = 2;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「小数部の桁数」が0以上、かつ「全体の桁数」未満、かつ31未満の整数
     * 「全体の桁数」に負の数が入っている
     *
     * @return void
     */
    public function testDecimal_MaxNumParamLessThan0()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = -1;
        $table_columns->decimal_part = 2;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「小数部の桁数」が0以上、かつ「全体の桁数」未満、かつ31未満の整数
     * 「全体の桁数」に小数が入っている
     *
     * @return void
     */
    public function testDecimal_MaxNumParamIsDecimal()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8.1;
        $table_columns->decimal_part = 2;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「全体の桁数」に文字列が入っている
     *
     * @return void
     */
    public function testDecimal_MaxNumParamIsString()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 'a';
        $table_columns->decimal_part = 2;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「小数部の桁数」が0より大きい、かつ「全体の桁数」未満、かつ31未満の整数
     * 「全体の桁数」にnullが入っている
     *
     * @return void
     */
    public function testDecimal_MaxNumParamIsNull()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = null;
        $table_columns->decimal_part = 2;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「全体の桁数」が0より大きい、かつ66未満の整数
     * 「小数部の桁数」に0が入っている
     *
     * @return void
     */
    public function testDecimal_DecimalPartParamEqual0()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 0;

        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(floatval(1), $result);
    }
    
    /**
     * Decimalへのキャスト
     * 「全体の桁数」が0より大きい、かつ66未満の整数
     * 「小数部の桁数」に「全体の桁数」より大きい
     *
     * @return void
     */
    public function testDecimal_DecimalPartParamGreaterThanMaxNumParam()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 9;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「全体の桁数」が0より大きい、かつ66未満の整数
     * 「小数部の桁数」が31以上の整数が入っている
     *
     * @return void
     */
    public function testDecimal_DecimalPartParamGreaterThanEqual31()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 35;
        $table_columns->decimal_part = 31;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「全体の桁数」が0より大きい、かつ66未満の整数
     * 「小数部の桁数」に負の数が入っている
     *
     * @return void
     */
    public function testDecimal_DecimalPartParamLessThan0()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = -1;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「全体の桁数」が0より大きい、かつ66未満の整数
     * 「小数部の桁数」に小数が入っている
     *
     * @return void
     */
    public function testDecimal_DecimalPartParamIsDecimal()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 0.1;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「全体の桁数」が0より大きい、かつ66未満の整数
     * 「小数部の桁数」に文字が入っている
     *
     * @return void
     */
    public function testDecimal_DecimalPartParamIsString()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = 'a';
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    /**
     * Decimalへのキャスト
     * 「全体の桁数」が0より大きい、かつ66未満の整数
     * 「小数部の桁数」にnullが入っている
     *
     * @return void
     */
    public function testDecimal_DecimalPartParamIsNull()
    {
        $target_value = '1.2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'decimal';
        $table_columns->maximum_number = 8;
        $table_columns->decimal_part = null;
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cast setting error');

        $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );
    }
    
    //date test
    /**
     * Dateへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が整数の場合（日付のみ）
     *
     * @return void
     */
    public function testDate_DataIsSerialNumber_Date()
    {
        $target_value = '42419';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('2016-02-19', $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が小数の場合（日付時刻形式）
     *
     * @return void
     */
    public function testDate_DataIsSerialNumber_DateTime()
    {
        $target_value = '42419.5242592593';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Dateへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が1未満の場合（時刻のみ）
     *
     * @return void
     */
    public function testDate_DataIsSerialNumber_Time()
    {
        $target_value = '0.9';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Dateへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が範囲外の場合
     *
     * @return void
     */
    public function testDate_DataIsSerialNumber_InvalidSerialNumberMinus()
    {
        $target_value = '-1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Dateへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が範囲外の場合
     *
     * @return void
     */
    public function testDate_DataIsSerialNumber_InvalidSerialNumberMoreThanMax()
    {
        $target_value = '2958466';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd）
     * 「対象データ」 が日付のみの場合
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_Date1Digit()
    {
        $target_value = '2019/1/2';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('2019-01-02', $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd）
     * 「対象データ」 が日付のみの場合
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_Date2Digit()
    {
        $target_value = '2019/01/02';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('2019-01-02', $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd hh:mm:ss）
     * 「対象データ」 が日付時間形式の場合
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_DateTime1Digit()
    {
        $target_value = '2019/1/2 03:04:05';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd hh:mm:ss）
     * 「対象データ」 が日付時間形式の場合
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_DateTime2Digit()
    {
        $target_value = '2019/01/02 03:04:05';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（hh:mm:ss）
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_Time()
    {
        $target_value = '12:34:56';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd）
     * 「対象データ」 が不正な日付
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_InvalidValue()
    {
        $target_value = '2019/13/32';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd）
     * 「対象データ」 が不正な日付
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_InvalidValueLessThanMin()
    {
        $target_value = '999/12/31';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd）
     * 「対象データ」 が不正な日付
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_ValidMinValue()
    {
        $target_value = '1000/01/01';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('1000-01-01', $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd）
     * 「対象データ」 が不正な日付
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_InvalidValueMoreThanMax()
    {
        $target_value = '10000/1/1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式の文字列（yyyy/mm/dd）
     * 「対象データ」 が不正な日付
     *
     * @return void
     */
    public function testDate_DataIsDateFormat_ValidMaxValue()
    {
        $target_value = '9999/12/31';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('9999-12-31', $result);
    }
    
    /**
     * Dateへのキャスト
     * 「対象データ」 が日付形式以外の文字列
     *
     * @return void
     */
    public function testDate_DataIsNotDateFormat()
    {
        $target_value = 'abc';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Dateへのキャスト
     * 「対象データ」 が不正な数（小数点が２つある）
     *
     * @return void
     */
    public function testDate_DataIsSerialNumber_InvalidSerialNumber()
    {
        $target_value = '295.84.66';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'date';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    //time test
    /**
     * Timeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testTime_DataIsSerialNumber_Time()
    {
        $target_value = '0.524259259259259';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('12:34:56', $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が日付時間形式の場合
     *
     * @return void
     */
    public function testTime_DataIsSerialNumber_DateTime()
    {
        $target_value = '12345.524259259259259';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が日付のみの場合
     *
     * @return void
     */
    public function testTime_DataIsSerialNumber_Date()
    {
        $target_value = '12345';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が範囲外の場合
     *
     * @return void
     */
    public function testTime_DataIsSerialNumber_InvalidValue()
    {
        $target_value = '-1';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm:ss）
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testTime_DataIsTimeFormat_TimeHHMMSS()
    {
        $target_value = '12:34:56';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('12:34:56', $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm:ss）
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testTime_DataIsTimeFormat_TimeHHMMSS_24HourNotation()
    {
        $target_value = '23:59:59';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('23:59:59', $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm:ss）
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testTime_DataIsTimeFormat_TimeHHMMSS1Digit()
    {
        $target_value = '1:02:03';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('01:02:03', $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm）
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testTime_DataIsTimeFormat_TimeHHMM()
    {
        $target_value = '12:34';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('12:34:00', $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm:ss）
     * 「対象データ」 が日付時間形式の場合
     *
     * @return void
     */
    public function testTime_DataIsDateTimeFormat_DateTimeHHMMSS()
    {
        $target_value = '2020/01/01 12:34:56';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm）
     * 「対象データ」 が日付時間形式の場合
     *
     * @return void
     */
    public function testTime_DataIsDateTimeFormat_DateTimeHHMM()
    {
        $target_value = '2020/01/01 12:34';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm）
     * 「対象データ」 が日付のみの場合
     *
     * @return void
     */
    public function testTime_DataIsDateFormat_Date()
    {
        $target_value = '2020/01/01';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm:ss）
     * 不正な時間
     *
     * @return void
     */
    public function testTime_DataIsTimeFormatHHMMSS_InvalidTime()
    {
        $target_value = '34:56:78';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列（hh:mm）
     * 不正な時間
     *
     * @return void
     */
    public function testTime_DataIsTimeFormatHHMM_InvalidTime()
    {
        $target_value = '34:56';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式（hh:mm）以外の文字列
     *
     * @return void
     */
    public function testTime_DataIsNotTimeFormat()
    {
        $target_value = 'abc';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が時間形式の文字列が「:」で終わっている
     *
     * @return void
     */
    public function testTime_InvalidTimeFormat()
    {
        $target_value = '12:34:';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Timeへのキャスト
     * 「対象データ」 が不正な数（小数点が２つある）
     *
     * @return void
     */
    public function testTime_InvalidSerialValue()
    {
        $target_value = '123.456.789';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'time';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    //datetime test
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testDateTime_DataIsSerialNumber_Time()
    {
        $target_value = '0.524259259259259';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Datetimeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testDateTime_DataIsSerialNumber_DateTime()
    {
        $target_value = '43831.5242592593';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('2020-01-01 12:34:56', $result);
    }

    /**
     * Datetimeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が日付のみの場合
     *
     * @return void
     */
    public function testDateTime_DataIsSerialNumber_Date()
    {
        $target_value = '43831';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('2020-01-01 00:00:00', $result);
    }

    /**
     * Datetimeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が範囲外の場合
     *
     * @return void
     */
    public function testDateTime_DataIsSerialNumber_InvalidValueLessThanMin()
    {
        $target_value = '999/12/31 23:59:59';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Datetimeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が範囲外の場合
     *
     * @return void
     */
    public function testDateTime_DataIsSerialNumber_ValidMinValue()
    {
        $target_value = '1000/01/01 00:00:00';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('1000-01-01 00:00:00', $result);
    }

    /**
     * Datetimeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が範囲外の場合
     *
     * @return void
     */
    public function testDateTime_DataIsSerialNumber_InvalidValueLessThanMax()
    {
        $target_value = '10000/01/01 00:00:00';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }

    /**
     * Datetimeへのキャスト
     * 「対象データ」 が数値（excelのシリアル値）の文字列
     * 「対象データ」 が範囲外の場合
     *
     * @return void
     */
    public function testDateTime_DataIsSerialNumber_ValidMaxValue()
    {
        $target_value = '9999/12/31 23:59:59';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('9999-12-31 23:59:59', $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が日付時間形式の文字列（yyyy/mm/dd）
     * 「対象データ」 が日付のみの場合
     *
     * @return void
     */
    public function testDateTime_DataIsDateFormat_Date()
    {
        $target_value = '2019/01/02';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('2019-01-02 00:00:00', $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が日付時間形式の文字列（秒あり）（yyyy/mm/dd hh:mm:ss）
     * 「対象データ」 が日付時間形式の場合
     *
     * @return void
     */
    public function testDateTime_DataIsDateTimeFormat_DateTImeHHMMSS()
    {
        $target_value = '2019/01/02 23:59:59';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('2019-01-02 23:59:59', $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が日付時間形式の文字列（秒なし）（yyyy/mm/dd hh:mm）
     * 「対象データ」 が日付時間形式の場合
     *
     * @return void
     */
    public function testDateTime_DataIsDateTimeFormat_DateTImeHHMM()
    {
        $target_value = '2019/01/02 23:59';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame('2019-01-02 23:59:00', $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が日付時間形式の文字列（hh:mm:ss）
     * 「対象データ」 が時間のみの場合
     *
     * @return void
     */
    public function testDateTime_DataIsTimeFormat_Time()
    {
        $target_value = '23:59:59';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が日付時間形式の文字列（yyyy/mm/dd hh:mm:ss）
     * 「対象データ」 が不正な日付
     *
     * @return void
     */
    public function testDateTime_DataIsTimeFormat_InvalidDate()
    {
        $target_value = '2020/13/34 23:59:59';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が日付時間形式の文字列（yyyy/mm/dd hh:mm:ss）
     * 「対象データ」 が不正な時間
     *
     * @return void
     */
    public function testDateTime_DataIsTimeFormat_InvalidTime()
    {
        $target_value = '2020/12/31 34:56:78';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が日付時間形式（hh:mm:ss）以外の文字列
     *
     * @return void
     */
    public function testDateTime_DataIsNotDateTimeFormat()
    {
        $target_value = 'abc';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が日付時間形式の文字列が「:」で終わっている
     *
     * @return void
     */
    public function testTime_InvalidDatetimeFormat()
    {
        $target_value = '2019/01/02 23:59:';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
    
    /**
     * Datetimeへのキャスト
     * 「対象データ」 が不正な数（小数点が２つある）
     *
     * @return void
     */
    public function testDateTime_InvalidSerialValue()
    {
        $target_value = '123.456.789';
        $table_columns = new TableColumns();
        $table_columns->data_type = 'datetime';
        
        $result = $this->casting_import_data->castData(
            $target_value,
            $table_columns
        );

        $this->assertSame(null, $result);
    }
}
