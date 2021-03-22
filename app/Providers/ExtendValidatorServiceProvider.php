<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ExtendValidatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('check_digit', function ($attribute, $value, $parameters, $validator) {
            return is_int($value) && $this->checkDigit($value);
        });
    }
    
    
    // modulus 10 weight 3-1
    private function checkDigit($data)
    {
        $str_data = strval($data);

        // 対象データは2桁以上必要（チェック対象データとチェックディジット）
        if (strlen($str_data) < 2) {
            return false;
        }

        $check_digit = mb_substr($str_data, strlen($str_data) - 1, 1);
        $calc_target = mb_substr($str_data, 0, strlen($str_data) - 1);

        $sum_value = 0;
        $calc_target_len = strlen($calc_target);

        // チェックディジットを計算する
        for ($i = 0; $i < $calc_target_len; $i++) {
            if ($i % 2 == 0) {
                $sum_value += intval($calc_target[$calc_target_len - $i - 1]) * 3;
            } else {
                $sum_value += intval($calc_target[$calc_target_len - $i - 1]);
            }
        }

        // 10から計算したチェックディジット（1の位の数）を引き、対象データのチェックディジットと比較する
        // 最後の mod 10 はチェックディジットが0だった場合に必要
        if ((10 - ($sum_value % 10)) % 10 == $check_digit) {
            $ret = true;
        } else {
            $ret = false;
        }
        
        return $ret;
    }
}
