<?php

namespace App\Services;

use DateTime;

/**
 * GENSがサポートしている日付、時間形式の文字列をDBに登録できる形式の文字列に変換する
 * 日付
 *  サポートしている形式：'yyyy/mm/dd'
 *  変換する形式：'yyyy-mm-dd'
 * 時間
 * サポートしている形式：'HH:MM:SS' または 'HH:MM'
 *  変換する形式：'HH:MM:SS'
 * 日付時間
 *  サポートしている形式：'yyyy/mm/dd HH:MM:SS' または 'yyyy/mm/dd HH:MM'
 *  変換する形式：'yyyy-mm-dd HH:MM:SS'
 *
 */
class ConvertingDateTimeFromStringService
{
    private const MYSQL_DATE_MIN = '1000-01-01';
    private const MYSQL_DATE_MAX = '9999-12-31';

    /**
     * キャスト対象データの日付形式の文字列（yyyy/mm/dd）を日付形式（yyyy-mm-dd）の文字列に変換する
     * 日付形式がyyyy/mm/dd以外の場合はnullを返す
     * 日付がMySQLのdate型で設定可能な範囲を超える場合はnullを返す
     * 日付が不正な場合はnullを返す
     *
     * @param ?string $data
     *
     * @return ?string
     */
    public function conversionToDateFromDateFormatString(?string $data): ?string
    {
        $ret = null;
        
        // currently supported format is only 'yyyy/mm/dd'
        $datetime = Datetime::createFromFormat('Y/m/d', $data);
        if ($datetime !== false) {
            // Check if the date is within the range to be registered in MySQL
            if (strtotime($this::MYSQL_DATE_MIN) <= strtotime($data) && strtotime($data) <= strtotime($this::MYSQL_DATE_MAX)) {
                list($Y, $m, $d) = explode('/', $data);
                // Check the validity of the date.
                if (checkdate($m, $d, $Y)) {
                    $ret = $datetime->format('Y-m-d');
                }
            }
        }

        return $ret;
    }
    
    /**
     * キャスト対象データの時間形式の文字列（HH:MM:SSまたはHH:MM）を時間形式（HH:MM:SS）の文字列に変換する
     * 日付形式がHH:MM:SSまたはHH:MM以外の場合はnullを返す
     * 時間が不正な場合はnullを返す
     *
     * @param ?string $data
     *
     * @return ?string
     */
    public function conversionToTimeFromTimeFormatString(?string $data)
    {
        $ret = null;
        // Check the validity of the time.
        if (strtotime($data) !== false) {
            $date = Datetime::createFromFormat('H:i:s', $data);
            if ($date !== false) {
                // Use "$date->format" to make formatting consistent with zeroes, even when the time is a single digit
                $ret = $date->format('H:i:s');
            } else {
                $date =  Datetime::createFromFormat('H:i', $data);
                if ($date !== false) {
                    // Change the format to one with seconds.
                    $ret = $date->format('H:i:s');
                }
            }
        }
        return $ret;
    }
    
    /**
     * キャスト対象データの日付時間形式の文字列（yyyy-mm-dd HH:MM(:SS)またはyyyy-mm-dd）を日付時間形式（yyyy-mm-dd HH:MM:SS）の文字列に変換する
     * 日付形式がyyyy/mm/dd以外の場合はnullを返す
     * 日付がMySQLのdate型で設定可能な範囲を超える場合はnullを返す
     * 日付が不正な場合はnullを返す
     * 日付形式がHH:MM:SSまたはHH:MM以外の場合はnullを返す
     * 時間が不正な場合はnullを返す
     *
     * @param ?string $data
     *
     * @return ?string
     */
    public function conversionToDatetimeFromDatetimeFormatString(?string $data): ?string
    {
        $ret = null;
        // currently supported format is only 'yyyy/mm/dd hh:mm(:ss)' or 'yyyy/mm/dd'
        $datetimeArray = explode(' ', $data);
        if (count($datetimeArray) === 1) {
            $date_part = $datetimeArray[0];
            $time_part = '00:00:00';
        } elseif (count($datetimeArray) === 2) {
            $date_part = $datetimeArray[0];
            $time_part = $datetimeArray[1];
        }
        if (isset($date_part) && isset($time_part)) {
            $date = $this->conversionToDateFromDateFormatString($date_part);
            $time = $this->conversionToTimeFromTimeFormatString($time_part);

            if ($date !== null && $time !== null) {
                $ret = $date . ' ' . $time;
            }
        }
        return $ret;
    }
}
