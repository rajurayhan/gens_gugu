<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SettingsCastDefinition;
use App\User;
use Illuminate\Database\Seeder;

class SetupSeeder extends Seeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /* Role */
        $roleAdmin = Role::updateOrCreate(
            ['name' => 'admin'],
            ['display_name' => '管理者']
        );
        $roleClerk = Role::updateOrCreate(
            ['name' => 'clerk'],
            ['display_name' => '店員']
        );
        $roleViewer = Role::updateOrCreate(
            ['name' => 'viewer'],
            ['display_name' => '視聴者']
        );

        /* Permission */
        /* Permission::updateOrCreate(
            [ 'name' => 'material-index' ],
            [ 'display_name' => 'Material Index' ]
        ); */
        /* Permission::updateOrCreate(
            [ 'name' => 'setting-index' ],
            [ 'display_name' => 'Setting Index' ]
        ); */

        /* User */
        $userAdmin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('12345678'),
                'is_admin' => true,
            ]
        );
        $userClerk = User::updateOrCreate(
            ['email' => 'clerk@example.com'],
            [
                'name' => 'Clerk User',
                'password' => bcrypt('12345678'),
            ]
        );
        $userViewer = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('12345678'),
            ]
        );

        /* Assign Role to User */
        // $userAdmin->attachRole( $roleAdmin );
        // $userClerk->attachRole( $roleClerk );
        // $userViewer->attachRole( $roleViewer );

        /* Setting */
        Setting::updateOrCreate(
            ['key' => 'header_background_color'],
            [
                'name' => 'Header Background Color',
                'value' => '#007bff',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'header_text_color'],
            [
                'name' => 'Header Text Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'footer_background_color'],
            [
                'name' => 'Footer Background Color',
                'value' => '#343a40',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'footer_text_color'],
            [
                'name' => 'Footer Text Color',
                'value' => '#6c757d',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'button_background_color'],
            [
                'name' => 'Button Background Color',
                'value' => '#007bff',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'button_text_color'],
            [
                'name' => 'Button Text Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_page_length'],
            [
                'name' => 'Material Table Page Length',
                'value' => 10,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_distinguish_between_half_and_full_width'],
            [
                'name' => 'Material Table Distinguish between Half or Full Width',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'numeric_values_truncate_or_round'],
            [
                'name' => 'Numeric Values Truncate or Round',
                'value' => 2,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'numeric_values_decimal_places'],
            [
                'name' => 'Numeric Values Decimal Places',
                'value' => 2,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_order_column'],
            [
                'name' => 'Material Table Order Column',
                'value' => 'nd',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_order_direction'],
            [
                'name' => 'Material Table Order Direction',
                'value' => 'desc',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_header_background_color'],
            [
                'name' => 'Material Table Header Background Color',
                'value' => '#1f487c',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_header_text_color'],
            [
                'name' => 'Material Table Header Text Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_header_height'],
            [
                'name' => 'Material Table Header Height',
                'value' => 100,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_a_width'],
            [
                'name' => 'Material Table Column 年度 Width',
                'value' => 20,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_b_width'],
            [
                'name' => 'Material Table Column 請求月 Width',
                'value' => 20,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_c_width'],
            [
                'name' => 'Material Table Column 題名 Width',
                'value' => 35,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_d_width'],
            [
                'name' => 'Material Table Column 種類 Width',
                'value' => 50,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_f_width'],
            [
                'name' => 'Material Table Column 売上名 Width',
                'value' => 100,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_h_width'],
            [
                'name' => 'Material Table Column 現場 Width',
                'value' => 100,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_j_width'],
            [
                'name' => 'Material Table Column 会社 Width',
                'value' => 85,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_l_width'],
            [
                'name' => 'Material Table Column NO Width',
                'value' => 30,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_m_width'],
            [
                'name' => 'Material Table Column 工事名 Width',
                'value' => 145,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_nd_width'],
            [
                'name' => 'Material Table Column 日付 Width',
                'value' => 70,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_r_width'],
            [
                'name' => 'Material Table Column 見積名 Width',
                'value' => 60,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_s_width'],
            [
                'name' => 'Material Table Column 工事内訳 Width',
                'value' => 20,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_t_width'],
            [
                'name' => 'Material Table Column 工事内訳２ Width',
                'value' => 35,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_u_width'],
            [
                'name' => 'Material Table Column 商品名 Width',
                'value' => 245,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_v_width'],
            [
                'name' => 'Material Table Column * Width',
                'value' => 35,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_w_width'],
            [
                'name' => 'Material Table Column 数量 Width',
                'value' => 35,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_x_width'],
            [
                'name' => 'Material Table Column 総重量 Width',
                'value' => 45,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_y_width'],
            [
                'name' => 'Material Table Column 単位 Width',
                'value' => 20,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_z_width'],
            [
                'name' => 'Material Table Column 単価 Width',
                'value' => 55,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_aa_width'],
            [
                'name' => 'Material Table Column 金額 Width',
                'value' => 55,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_ab_width'],
            [
                'name' => 'Material Table Column 材積㎡単価 Width',
                'value' => 35,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_file_name_width'],
            [
                'name' => 'Material Table Column ファイル名 Width',
                'value' => 50,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_a_visibility'],
            [
                'name' => 'Material Table Column 年度 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_b_visibility'],
            [
                'name' => 'Material Table Column 請求月 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_c_visibility'],
            [
                'name' => 'Material Table Column 題名 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_d_visibility'],
            [
                'name' => 'Material Table Column 種類 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_f_visibility'],
            [
                'name' => 'Material Table Column 売上名 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_h_visibility'],
            [
                'name' => 'Material Table Column 現場 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_j_visibility'],
            [
                'name' => 'Material Table Column 会社 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_l_visibility'],
            [
                'name' => 'Material Table Column NO Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_m_visibility'],
            [
                'name' => 'Material Table Column 工事名 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_nd_visibility'],
            [
                'name' => 'Material Table Column 日付 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_r_visibility'],
            [
                'name' => 'Material Table Column 見積名 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_s_visibility'],
            [
                'name' => 'Material Table Column 工事内訳 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_t_visibility'],
            [
                'name' => 'Material Table Column 工事内訳２ Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_u_visibility'],
            [
                'name' => 'Material Table Column 商品名 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_v_visibility'],
            [
                'name' => 'Material Table Column * Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_w_visibility'],
            [
                'name' => 'Material Table Column 数量 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_x_visibility'],
            [
                'name' => 'Material Table Column 総重量 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_y_visibility'],
            [
                'name' => 'Material Table Column 単位 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_z_visibility'],
            [
                'name' => 'Material Table Column 単価 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_aa_visibility'],
            [
                'name' => 'Material Table Column 金額 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_ab_visibility'],
            [
                'name' => 'Material Table Column 材積㎡単価 Visibility',
                'value' => true,
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'material_table_column_file_name_visibility'],
            [
                'name' => 'Material Table Column ファイル名 Visibility',
                'value' => true,
            ]
        );
        /* Setting::updateOrCreate(
            [ 'key' => 'material_table_column_a_background_color' ],
            [
                'name' => 'Material Table Column 年度 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_b_background_color' ],
            [
                'name' => 'Material Table Column 請求月 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_c_background_color' ],
            [
                'name' => 'Material Table Column 題名 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_d_background_color' ],
            [
                'name' => 'Material Table Column 種類 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_f_background_color' ],
            [
                'name' => 'Material Table Column 売上名 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_h_background_color' ],
            [
                'name' => 'Material Table Column 現場 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_j_background_color' ],
            [
                'name' => 'Material Table Column 会社 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_l_background_color' ],
            [
                'name' => 'Material Table Column NO Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_m_background_color' ],
            [
                'name' => 'Material Table Column 工事名 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_nd_background_color' ],
            [
                'name' => 'Material Table Column 日付 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_r_background_color' ],
            [
                'name' => 'Material Table Column 見積名 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_s_background_color' ],
            [
                'name' => 'Material Table Column 工事内訳 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_t_background_color' ],
            [
                'name' => 'Material Table Column 工事内訳２ Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_u_background_color' ],
            [
                'name' => 'Material Table Column 商品名 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_v_background_color' ],
            [
                'name' => 'Material Table Column * Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_w_background_color' ],
            [
                'name' => 'Material Table Column 数量 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_x_background_color' ],
            [
                'name' => 'Material Table Column 総重量 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_y_background_color' ],
            [
                'name' => 'Material Table Column 単位 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_z_background_color' ],
            [
                'name' => 'Material Table Column 単価 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_aa_background_color' ],
            [
                'name' => 'Material Table Column 金額 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_ab_background_color' ],
            [
                'name' => 'Material Table Column 材積㎡単価 Background Color',
                'value' => '#ffffff',
            ]
        );
        Setting::updateOrCreate(
            [ 'key' => 'material_table_column_file_name_background_color' ],
            [
                'name' => 'Material Table Column ファイル名 Background Color',
                'value' => '#ffffff',
            ]
        ); */
        Setting::updateOrCreate(
            ['key' => 'material_table_column_nd_format'],
            [
                'name' => 'Material Table Column 日付 Format',
                'value' => 2,
            ]
        );
    }
}
