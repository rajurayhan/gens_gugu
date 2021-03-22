<?php

namespace App\Services;

use DateTime;
use App\Exceptions\Exception;
use Illuminate\Support\Facades\Log;

class CastingImportData
{
    private const DECIMAL_ALL_DIGIT = 65;
    private const DECIMAL_DECIMAL_DIGIT = 30;
    private const EXCEPTION_MESSAGE = 'cast setting error';
    private const EXCEL_SERIAL_NUM_MAX = 2958465;   // = 9999/12/31 Same as the mysql's maximum date type

    private const INT_RANGE = [
        'tinyint' => ['min' => -128, 'max' => 127],
        'int' => ['min' => -2147483648, 'max' => 2147483647],
        'bigint' => ['min' => PHP_INT_MIN, 'max' => PHP_INT_MAX],
    ];

    /**
     * $typeに設定されたパラメータに合わせて$dataをキャストした結果を返す
     *
     * @param string $data
     * @param \App\Models\DatasourceColumns $type
     *
     * @return mixed
     */
    public function castData(?string $data, $type)
    {
        $ret = null;
        
        $converting_datetime = resolve(ConvertingDateTimeFromStringService::class);

        switch ($type->data_type) {
            case 'varchar':
            case 'char':
                if ($this->verifySettingValue($type->length)) {
                    if (mb_strlen($data) <= $type->length) {
                        $ret = $data;
                    } else {
                        $ret = mb_substr($data, 0, $type->length);
                    }
                } else {
                    Log::error('"' . $type->data_type . '" requires "length" setting');
                    throw new Exception($this::EXCEPTION_MESSAGE);
                }
                break;

            case 'tinyint':
            case 'int':
                if ($this->checkValueWithinRangeForIntAndTinyint($data, $type->data_type)) {
                    $ret = $this->getIntegerPartAsIntType($data);
                } else {
                    $ret = null;
                }
                break;

            case 'bigint':
                $ret = $this->getIntegerPartAsIntType($data);
                break;

            case 'decimal':
                if ($this->verifyDecimalSettingValue($type)) {
                    $ret = $this->conversionToDecimal($data, $type);
                } else {
                    Log::error('"' . $type->data_type . '" requires "maximum_number" and "decimal_part" setting');
                    throw new Exception($this::EXCEPTION_MESSAGE);
                }
                break;

            case 'date':
                if (is_numeric($data)) {
                    $ret = $this->conversionToDateFromSerialNumber($data);
                } else {
                    $ret = $converting_datetime->conversionToDateFromDateFormatString($data);
                }
                break;

            case 'time':
                if (is_numeric($data)) {
                    if ($this->checkSerialNumberWithinRangeForTime($data)) {
                        $ret = $this->conversionToTimeFromSerialNumber($data);
                    }
                } else {
                    if ($this->checkTimeStringWithinRangeForTime($data)) {
                        $ret = $converting_datetime->conversionToTimeFromTimeFormatString($data);
                    }
                }
                break;

            case 'datetime':
                if (is_numeric($data)) {
                    $ret = $this->conversionToDatetimeFromSerialNumber($data);
                } else {
                    $ret = $converting_datetime->conversionToDatetimeFromDatetimeFormatString($data);
                }
                break;

            default:
                Log::warning('not support data_type: "' . $type->data_type . '"');
                break;
        }

        return $ret;
    }

    /**
     * 数字の文字列から整数部分を抜き出してint型で返す
     * PHP_INT_MIN, PHP_INT_MAXの範囲外であったり、数値として認識できない場合はnullを返す
     *
     * @param ?string $data
     *
     * @return ?int
     */

    /**
     * パラメータ（length, maximum_number, decimal part）が不正な値でないかをチェックする
     *
     * @param $settingValue
     *
     * @return bool
     */
    private function verifySettingValue($settingValue): bool
    {
        return !is_null($settingValue) && is_numeric($settingValue) && is_int($settingValue) && $settingValue >= 0;
    }

    /**
     * パラメータ（maximum_number, decimal part）が不正な値でないかをチェックする
     *
     * @param \App\Models\DatasourceColumns $type
     *
     * @return bool
     */
    private function verifyDecimalSettingValue($type): bool
    {
        if ($this->verifySettingValue($type->maximum_number) && $this->verifySettingValue($type->decimal_part)) {
            if ($type->maximum_number <= $this::DECIMAL_ALL_DIGIT &&
                $type->decimal_part <= $this::DECIMAL_DECIMAL_DIGIT &&
                $type->decimal_part <= $type->maximum_number
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * キャスト対象データが型の範囲内かどうかをチェックする
     *
     * @param ?string $value
     * @param string $int_type
     *
     * @return bool
     */
    private function checkValueWithinRangeForIntAndTinyint(?string $value, string $int_type): bool
    {
        $result = false;

        // Check whether decimal point fits into the range or not
        // Use min-1 and max+1 because it has to check the value after rounded down
        if (is_numeric($value) &&
            $value > $this::INT_RANGE[$int_type]['min'] - 1 &&
            $value < $this::INT_RANGE[$int_type]['max'] + 1) {
                $result = true;
        }

        return $result;
    }
    
    /**
     * キャスト対象データ（Excelシリアル値の文字列）がtime型でサポートする範囲内かどうかをチェックする
     * 1以上の値は日付情報を含むため対象外
     *
     * @param ?string $value
     *
     * @return bool
     */
    private function checkSerialNumberWithinRangeForTime(?string $value): bool
    {
        return 0 <= $value && $value < 1;
    }
    
    /**
     * キャスト対象データ（時間形式の文字列）がtime型でサポートする範囲内かどうかをチェックする
     *
     * @param ?string $value
     *
     * @return bool
     */
    private function checkTimeStringWithinRangeForTime(?string $value)
    {
        return strtotime($value) !== false;
    }

    /**
     * Extracts the integer part from a string of numbers and returns an int type
     * Round down to the nearest decimal.
     * Returns null if the value is out of PHP_INT_MIN and PHP_INT_MAX or cannot be converted to int type.
     *
     * @param ?string $data
     *
     * @return ?int
     */
    private function getIntegerPartAsIntType(?string $data): ?int
    {
        if (is_numeric($data)) {
            $len_integer_part = strpos($data, '.');
            // As the usual inequality comparison does not allow us to check if
            // the value fits between PHP_INT_MAX and PHP_INT_MIN, we need to use filter_var for the conversion
            if ($len_integer_part === false) {
                $int_val = filter_var($data, FILTER_VALIDATE_INT);
            } else {
                // To cut down to a decimal point, cut out the '.'
                $int_val = filter_var(substr($data, 0, $len_integer_part), FILTER_VALIDATE_INT);
            }
            if ($int_val === false) {
                // if the value is not an integer, or if the value is not between PHP_INT_MAX and PHP_INT_MIN
                $ret = null;
            } else {
                $ret = intval($int_val);
            }
        } else {
            $ret = null;
        }
        return $ret;
    }

    /**
     * キャスト対象データ（数字の文字列）をfloatに変換する
     * 小数部分の桁数がパラメータ（decimal_part）より大きい値場合は切り捨てる
     * 整数部分の桁数がパラメータ（maximum_number）より大きい値場合はnullを返す
     * PHP_INT_MAXより大きい数値の場合はnullを返す
     *
     * @param ?string $data
     * @param \App\Models\DatasourceColumns $type
     *
     * @return ?float
     */
    private function conversionToDecimal(?string $data, $type): ?float
    {
        if (is_numeric($data) && intval($data) <= PHP_INT_MAX) {
            $decimal_point_pos = strpos($data, '.');
            $minus_sign_pos = strpos($data, '-');
            if ($decimal_point_pos === false) {
                $len_integer_part = strlen($data);
            } else {
                $len_integer_part = $decimal_point_pos;
            }
            // If the number is negative($minus_sign_pos === 0), decrease one letter of the minus sign.
            if ($minus_sign_pos !== false) {
                $len_integer_part--;
            }

            if ($decimal_point_pos === false) {
                // $data doesn't have decimal point
                if ($len_integer_part <= $type->maximum_number) {
                    $ret = floatval($data);
                } else {
                    $ret = null;
                }
            } else {
                // $data has decimal point
                if ($len_integer_part <= $type->maximum_number - $type->decimal_part) {
                    // rounding down the decimal point
                    // Since $decimal_point_pos is 0-origin, it should be +1
                    $ret = floatval(substr($data, 0, $decimal_point_pos + $type->decimal_part + 1));
                } else {
                    // The number of digits in the integer portion exceeds the set value.
                    $ret = null;
                }
            }
        } else {
            // The number of digits in the integer portion exceeds the set value.
            $ret = null;
        }
        return $ret;
    }

    /**
     * キャスト対象データ（Excelシリアル値の文字列）を日付形式（yyyy-mm-dd）の文字列に変換する
     * 時間情報を含んでいる（小数値の文字列）場合は、nullを返す
     * キャスト対象データが1未満の場合（1900/01/00）は、nullを返す
     * キャスト対象データが最大値（9999/12/31）より大きい場合は、nullを返す
     *
     * @param ?string $data
     *
     * @return ?string
     */
    private function conversionToDateFromSerialNumber(?string $data): ?string
    {
        $ret = null;

        // Check if it's an integer, and if it's within the range of the INT type.
        $int_val = filter_var($data, FILTER_VALIDATE_INT);
        // extract an integer
        $serialNumber = $this->getIntegerPartAsIntType($data);
        // Check that it can be registered with mysql.
        // If the serial value of Excel is 0, it means 1900/01/00. This is an error in mysql, so set it to null
        if ($int_val !== false && $serialNumber !== null && 1 <= $serialNumber && $serialNumber <= $this::EXCEL_SERIAL_NUM_MAX) {
            $ret = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data)->format('Y-m-d');
        }

        return $ret;
    }
    
    /**
     * キャスト対象データ（Excelシリアル値の文字列）を時間形式の文字列（HH:MM:SS）に変換する
     *
     * @param ?string $data
     *
     * @return string
     */
    private function conversionToTimeFromSerialNumber(?string $data): string
    {
        return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data)->format('H:i:s');
    }
    
    /**
     * キャスト対象データ（Excelシリアル値の文字列）を日付時間形式の文字列（yyyy/mm/dd HH:MM:SS）に変換する
     * 日付の値が不正な場合はnullを返す
     *
     * @param ?string $data
     *
     * @return ?string
     */
    private function conversionToDatetimeFromSerialNumber(?string $data): ?string
    {
        $ret = null;
        $decimal_point_pos = strpos($data, '.');
        if ($decimal_point_pos === false) {
            $date_part = $data;
        } else {
            $date_part = $this->getIntegerPartAsIntType($data);
        }
        $date = $this->conversionToDateFromSerialNumber($date_part);
        $time = $this->conversionToTimeFromSerialNumber($data);

        if ($date !== null && $time !== null) {
            $ret = $date . ' ' . $time;
        }
        return $ret;
    }
}
