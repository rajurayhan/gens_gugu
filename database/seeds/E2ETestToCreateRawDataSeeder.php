<?php

use Illuminate\Database\Seeder;

class E2ETestToCreateRawDataSeeder extends Seeder
{
    // テスト用に作成するテーブル名
    // このテーブル名を基にmasterテーブルから既存のテストデータ削除を行うため基本的に変更不可
    // 変更する場合は、変更前のテストデータを削除してから変更すること
    private static $rawData1 = 'xls_for_e2e_test';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table(static::$rawData1)->truncate();

        // テストケースに必要なデータをここで追加
        $test_data = [];
        for ($i = 1; $i <= 1100; $i++) {
            array_push(
                $test_data,
                [
                'file_name' => 'ファイル名1',
                'file_id' => 1,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
                'test_column_bigint' => $i,
                'test_column_date' => '2020-07-29 14:21:24',
                'test_column_varchar' => '文字列' . $i,
                'test_column_decimal' => $i * 0.1,
                'test_column_datetime' => '2020-07-29 14:21:24',
                'test_column_comma' => 'some,example,with,comma'. $i,
                'test_column_double_quotation' => 'double"quotation text" examples'. $i,
                'test_column_line_break' => "This is an \n example with \n two line breaks.". $i,
                'test_column_mdc' => '♂♀'. $i,
                'test_column_half_width_kana' => 'ｱｲｳｴｵ'. $i,
                ]
            );
        }
        //Add some new data required for the test case
        array_push(
            $test_data,
            [
                'file_name' => 'ファイル名1',
                'file_id' => 1,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
                'test_column_bigint' => 5458364,
                'test_column_date' => '2020-07-29 14:21:24',
                'test_column_varchar' => 'Normal text for varchar',
                'test_column_decimal' => 56.2035,
                'test_column_datetime' => '2020-07-29 14:21:24',
                'test_column_comma' => 'some,example,with,comma',
                'test_column_double_quotation' => 'double"quotation text" examples',
                'test_column_line_break' => "This is an \n example with \n two line breaks.",
                'test_column_mdc' => '♂♀',
                'test_column_half_width_kana' => 'ｱｲｳｴｵ',
            ],
            [
                'file_name' => 'ファイル名1',
                'file_id' => 1,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
                'test_column_bigint' => -9223372036854775807,
                'test_column_date' => '2020-07-29 14:21:24',
                'test_column_varchar' => 'a',
                'test_column_decimal' => 0,
                'test_column_datetime' => '2020-07-29 14:21:24',
                'test_column_comma' => 'some,example,with,comma2',
                'test_column_double_quotation' => 'double"quotation text" examples2',
                'test_column_line_break' => "This is an \n example with \n two line breaks2.",
                'test_column_mdc' => '♂♀',
                'test_column_half_width_kana' => 'ｱｲｳｴｵ',
            ],
            [
                'file_name' => 'ファイル名1',
                'file_id' => 1,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
                'test_column_bigint' => 9223372036854775807,
                'test_column_date' => '2020-07-29 14:21:24',
                'test_column_varchar' => 'Maximum charecter length examples... klnsduhfusdhjflhsdiuytejrtiodfhuihgiosdhiughsuidhfhfdgnjsfdghduirgg klnsduhfusdhjflhsdiuytejrtiodfhuihgiosdhiughsuidhfhfdgnjsfdghduirgg klnsduhfusdhjflhsdiuytejrtiodfhuihgiosdhiughsuidhfhfdgnjsfdghduirklnsduhfusdhjflhs',
                'test_column_decimal' => 99955588877711122239995558887712315.156464655646498629849562955952,
                'test_column_datetime' => '2020-07-29 14:21:24',
                'test_column_comma' => 'some,example,with,some,comma3',
                'test_column_double_quotation' => 'double"quotation text" examples3',
                'test_column_line_break' => "This is an \n example with \n two line breaks3.",
                'test_column_mdc' => '♂♀',
                'test_column_half_width_kana' => 'ｱｲｳｴｵ',
            ],
            [
                'file_name' => 'ファイル名1',
                'file_id' => 1,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
                'test_column_bigint' => 1,
                'test_column_date' => '2020-07-29 14:21:24',
                'test_column_varchar' => '',
                'test_column_decimal' => 5.0,
                'test_column_datetime' => '2020-07-29 14:21:24',
                'test_column_comma' => 'some,example,with,comma4',
                'test_column_double_quotation' => 'double"quotation text" examples4',
                'test_column_line_break' => "This is an \n example with \n two line breaks4.",
                'test_column_mdc' => '♂♀',
                'test_column_half_width_kana' => 'ｱｲｳｴｵ',
            ],
            [
                'file_name' => 'ファイル名1',
                'file_id' => 1,
                'created_by' => null,
                'created_at' => '2020-07-29 14:21:24',
                'updated_by' => null,
                'updated_at' => '2020-07-29 14:21:24',
                'test_column_bigint' => null,
                'test_column_date' => null,
                'test_column_varchar' => null,
                'test_column_decimal' => null,
                'test_column_datetime' => null,
                'test_column_comma' => 'some,example,with,comma5',
                'test_column_double_quotation' => 'double"quotation text" examples5',
                'test_column_line_break' => "This is an \n example with \n two line breaks5.",
                'test_column_mdc' => '♂♀',
                'test_column_half_width_kana' => 'ｱｲｳｴｵ',
            ]
        );

        DB::table('xls_for_e2e_test')->insert($test_data);
    }
}
