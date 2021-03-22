<?php

namespace App\Libraries;

use Exception;

class Utility
{
    /**
     * パラメータの配列の中からサポートしていないパラメータの配列を返す
     * 
     * @param array $target リクエストパラメータの配列 $request->all()
     * @param array $supported_parameters サポートしているパラメータの配列
     * @return array $targetのパラメータのうち、$supported_paramsに含まれないパラメータの配列
     */
    public static function getUnsupportParameters(array $target, array $supported_parameters)
    {

        // 添字配列かどうかをチェック。空配列の場合は空の連想配列として処理する
        if (count($target) != 0 && array_values($target) === $target) {
            // 添字配列は未対応。呼び出し箇所の実装ミスなので、例外を投げる
            throw new Exception();
        }
        return array_values(array_diff(array_keys($target), $supported_parameters));
    }

    // Converts Excel Column Alphabetic Value into Numeric value. 
    public static function alpha2num($columnAlphabetic)
    {
        $numericValue = 0;
        foreach (str_split($columnAlphabetic) as $singleLetter) {
            $numericValue = ($numericValue * 26) + (ord(strtolower($singleLetter)) - 96);
        }
        return $numericValue;
    }

    // Converts Numbers into Excel Column Alphabetic Value. 
    public static function num2alpha($columnNumeric)
    {
        $numeric    = ($columnNumeric - 1) % 26;
        $letter     = chr(65 + $numeric);
        $numberTwo  = intval(($columnNumeric - 1) / 26);
        if ($numberTwo > 0) {
            return self::num2alpha($numberTwo) . $letter;
        } else {
            return $letter;
        }
    }
}
