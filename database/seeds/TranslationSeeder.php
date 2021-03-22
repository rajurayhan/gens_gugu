<?php

use Spatie\TranslationLoader\LanguageLine;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        LanguageLine::updateOrCreate(
            ['group' => 'app', 'key' => 'name'],
            [
                'text'  => [
                    'en' => 'Material Management System',
                    'ja' => '資材管理システム '
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'app', 'key' => 'processing'],
            [
                'text'  => [
                    'en' => 'Processing, Please Wait...',
                    'ja' => '処理中、しばらくお待ちください... '
                ]
            ]
        );

        // begin login
        LanguageLine::updateOrCreate(
            ['group' => 'login', 'key' => 'page_title'],
            [
                'text'  => [
                    'en' => 'Login',
                    'ja' => 'ログイン'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'login', 'key' => 'heading'],
            [
                'text'  => [
                    'en' => 'Login',
                    'ja' => 'ログイン'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'login', 'key' => 'form_email_label'],
            [
                'text'  => [
                    'en' => 'E-mail address',
                    'ja' => '電子メールアドレス'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'login', 'key' => 'form_password_label'],
            [
                'text'  => [
                    'en' => 'Password',
                    'ja' => 'パスワード'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'login', 'key' => 'form_submit_button_name'],
            [
                'text'  => [
                    'en' => 'Login',
                    'ja' => 'ログイン'
                ]
            ]
        );
        // end login

        // begin header
        LanguageLine::updateOrCreate(
            ['group' => 'header', 'key' => 'locale_menu_title'],
            [
                'text'  => [
                    'en' => 'Locale',
                    'ja' => 'ロケール'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'header', 'key' => 'translation_menu_title'],
            [
                'text'  => [
                    'en' => 'Translation',
                    'ja' => '翻訳'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'header', 'key' => 'user_icon_title'],
            [
                'text'  => [
                    'en' => 'User',
                    'ja' => 'ユーザー'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'header', 'key' => 'file_icon_title'],
            [
                'text'  => [
                    'en' => 'File',
                    'ja' => 'ファイル'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'header', 'key' => 'setting_icon_title'],
            [
                'text'  => [
                    'en' => 'Setting',
                    'ja' => 'セッティング'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'header', 'key' => 'change_password_menu_title'],
            [
                'text'  => [
                    'en' => 'Change password',
                    'ja' => 'パスワードを変更する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'header', 'key' => 'logout_menu_title'],
            [
                'text'  => [
                    'en' => 'Logout',
                    'ja' => 'ログアウト'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'modal_change_password', 'key' => 'heading'],
            [
                'text'  => [
                    'en' => 'Change password',
                    'ja' => 'パスワードを変更する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_change_password', 'key' => 'email_label'],
            [
                'text'  => [
                    'en' => 'Email',
                    'ja' => 'Eメール'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_change_password', 'key' => 'password_label'],
            [
                'text'  => [
                    'en' => 'Password',
                    'ja' => 'パスワード'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_change_password', 'key' => 'confirm_password_label'],
            [
                'text'  => [
                    'en' => 'Confirm Password',
                    'ja' => 'パスワードを認証する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_change_password', 'key' => 'button_name'],
            [
                'text'  => [
                    'en' => 'Change password',
                    'ja' => 'パスワードを変更する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_change_password', 'key' => 'success_alert'],
            [
                'text'  => [
                    'en' => 'Password has been changed.',
                    'ja' => 'パスワード変更済み。'
                ]
            ]
        );
        // end header

        // begin footer
        LanguageLine::updateOrCreate(
            ['group' => 'footer', 'key' => 'developed_by_title'],
            [
                'text'  => [
                    'en' => 'Developed by',
                    'ja' => 'によって開発された'
                ]
            ]
        );
        // end footer

        // begin material index
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'page_title'],
            [
                'text'  => [
                    'en' => 'Material List',
                    'ja' => '材料リスト'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'table_title'],
            [
                'text'  => [
                    'en' => 'Material List',
                    'ja' => '材料リスト'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'export_button_title'],
            [
                'text'  => [
                    'en' => 'Export',
                    'ja' => '輸出する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_product_name_label'],
            [
                'text'  => [
                    'en' => 'Product name',
                    'ja' => '商品名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_product_name_placeholder'],
            [
                'text'  => [
                    'en' => 'Product name',
                    'ja' => '商品名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_year_label'],
            [
                'text'  => [
                    'en' => 'Year',
                    'ja' => '年度'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_billing_month_label'],
            [
                'text'  => [
                    'en' => 'Billing month',
                    'ja' => '請求月'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_title_label'],
            [
                'text'  => [
                    'en' => 'Title',
                    'ja' => '題名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_title_placeholder'],
            [
                'text'  => [
                    'en' => 'Title',
                    'ja' => '題名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_type_label'],
            [
                'text'  => [
                    'en' => 'Type',
                    'ja' => '種類'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_type_placeholder'],
            [
                'text'  => [
                    'en' => 'Type',
                    'ja' => '種類'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_sales_name_label'],
            [
                'text'  => [
                    'en' => 'Sales name',
                    'ja' => '売上名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_sales_name_placeholder'],
            [
                'text'  => [
                    'en' => 'Sales name',
                    'ja' => '売上名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_site_label'],
            [
                'text'  => [
                    'en' => 'Site',
                    'ja' => '現場'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_site_placeholder'],
            [
                'text'  => [
                    'en' => 'Site',
                    'ja' => '現場'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_company_label'],
            [
                'text'  => [
                    'en' => 'Company',
                    'ja' => '会社'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_company_placeholder'],
            [
                'text'  => [
                    'en' => 'Company',
                    'ja' => '会社'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_no_label'],
            [
                'text'  => [
                    'en' => 'NO',
                    'ja' => 'いいえ'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_no_placeholder'],
            [
                'text'  => [
                    'en' => 'NO',
                    'ja' => 'いいえ'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_construction_name_label'],
            [
                'text'  => [
                    'en' => 'Construction name',
                    'ja' => '工事名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_construction_name_placeholder'],
            [
                'text'  => [
                    'en' => 'Construction name',
                    'ja' => '工事名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_reset_button_title'],
            [
                'text'  => [
                    'en' => 'Reset',
                    'ja' => 'リセット'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'search_submit_button_title'],
            [
                'text'  => [
                    'en' => 'Search',
                    'ja' => '検索'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'export_failed_large_file_error_alert'],
            [
                'text'  => [
                    'en' => 'Export to excel failed for trying to export large data set',
                    'ja' => '大きなデータセットをエクスポートしようとしてExcelにエクスポートできませんでした'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'material_index', 'key' => 'export_failed_error_alert'],
            [
                'text'  => [
                    'en' => 'Export to excel failed',
                    'ja' => 'Excelへのエクスポートに失敗しました'
                ]
            ]
        );
        // end material index

        // begin translation index
        LanguageLine::updateOrCreate(
            ['group' => 'translation_index', 'key' => 'page_title'],
            [
                'text'  => [
                    'en' => 'Translation list',
                    'ja' => '翻訳リスト'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'translation_index', 'key' => 'table_title'],
            [
                'text'  => [
                    'en' => 'Translation list',
                    'ja' => '翻訳リスト'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'translation_index', 'key' => 'table_id_column_title'],
            [
                'text'  => [
                    'en' => 'ID',
                    'ja' => 'ID'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'translation_index', 'key' => 'table_group_column_title'],
            [
                'text'  => [
                    'en' => 'Group',
                    'ja' => 'グループ'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'translation_index', 'key' => 'table_key_column_title'],
            [
                'text'  => [
                    'en' => 'Key',
                    'ja' => 'キー'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'translation_index', 'key' => 'table_text_column_title'],
            [
                'text'  => [
                    'en' => 'Text',
                    'ja' => 'テキスト'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'translation_index', 'key' => 'table_action_column_title'],
            [
                'text'  => [
                    'en' => 'Action',
                    'ja' => 'アクション'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'translation_index', 'key' => 'table_edit_button_title'],
            [
                'text'  => [
                    'en' => 'Edit',
                    'ja' => '編集する'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'modal_translation_edit', 'key' => 'success_alert'],
            [
                'text'  => [
                    'en' => 'Translation updated successfully',
                    'ja' => '翻訳は正常に更新されました'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_translation_edit', 'key' => 'heading'],
            [
                'text'  => [
                    'en' => 'Edit translation',
                    'ja' => '翻訳を編集する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_translation_edit', 'key' => 'button_name'],
            [
                'text'  => [
                    'en' => 'Update',
                    'ja' => '更新'
                ]
            ]
        );
        // end translation index

        // begin user index
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'page_title'],
            [
                'text'  => [
                    'en' => 'User List',
                    'ja' => 'ユーザーリスト'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_title'],
            [
                'text'  => [
                    'en' => 'User List',
                    'ja' => 'ユーザーリスト'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'user_create_icon_title'],
            [
                'text'  => [
                    'en' => 'Create user',
                    'ja' => 'ユーザーを作成'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_id_column_title'],
            [
                'text'  => [
                    'en' => 'ID',
                    'ja' => 'ID'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_name_column_title'],
            [
                'text'  => [
                    'en' => 'Name',
                    'ja' => '名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_email_column_title'],
            [
                'text'  => [
                    'en' => 'Email',
                    'ja' => 'Eメール'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_role_column_title'],
            [
                'text'  => [
                    'en' => 'Role',
                    'ja' => '役割'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_created_at_column_title'],
            [
                'text'  => [
                    'en' => 'Created At',
                    'ja' => '作成日'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_action_column_title'],
            [
                'text'  => [
                    'en' => 'Action',
                    'ja' => 'アクション'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_edit_button_name'],
            [
                'text'  => [
                    'en' => 'Edit',
                    'ja' => '編集する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_delete_button_name'],
            [
                'text'  => [
                    'en' => 'Delete',
                    'ja' => '削除する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'user_index', 'key' => 'table_edit_password_button_name'],
            [
                'text'  => [
                    'en' => 'Edit Password',
                    'ja' => 'パスワードを編集'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_add', 'key' => 'heading'],
            [
                'text'  => [
                    'en' => 'Create user',
                    'ja' => 'ユーザーを作成'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_add', 'key' => 'success_alert'],
            [
                'text'  => [
                    'en' => 'User added successfully',
                    'ja' => 'ユーザーを追加しました'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_add', 'key' => 'name_label'],
            [
                'text'  => [
                    'en' => 'Name',
                    'ja' => '名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_add', 'key' => 'email_label'],
            [
                'text'  => [
                    'en' => 'Email',
                    'ja' => 'Eメール'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_add', 'key' => 'role_label'],
            [
                'text'  => [
                    'en' => 'Role',
                    'ja' => '役割'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_add', 'key' => 'password_label'],
            [
                'text'  => [
                    'en' => 'Password',
                    'ja' => 'パスワード'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_add', 'key' => 'confirm_password_label'],
            [
                'text'  => [
                    'en' => 'Confirm Password',
                    'ja' => 'パスワードを認証する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_add', 'key' => 'submit_button_name'],
            [
                'text'  => [
                    'en' => 'Submit',
                    'ja' => '提出する'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit', 'key' => 'success_alert'],
            [
                'text'  => [
                    'en' => 'User updated successfully',
                    'ja' => 'ユーザーは正常に更新されました'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit', 'key' => 'heading'],
            [
                'text'  => [
                    'en' => 'Edit user',
                    'ja' => 'ユーザーを編集'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit', 'key' => 'name_label'],
            [
                'text'  => [
                    'en' => 'Name',
                    'ja' => '名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit', 'key' => 'email_label'],
            [
                'text'  => [
                    'en' => 'Email',
                    'ja' => 'Eメール'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit', 'key' => 'role_label'],
            [
                'text'  => [
                    'en' => 'Role',
                    'ja' => '役割'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit', 'key' => 'update_button_name'],
            [
                'text'  => [
                    'en' => 'Update',
                    'ja' => '更新'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_delete', 'key' => 'heading'],
            [
                'text'  => [
                    'en' => 'Delete user',
                    'ja' => 'ユーザーを削除'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_delete', 'key' => 'question'],
            [
                'text'  => [
                    'en' => 'Do you really want to delete this?',
                    'ja' => '本当に削除しますか？'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_delete', 'key' => 'button_name'],
            [
                'text'  => [
                    'en' => 'Delete',
                    'ja' => '削除する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_delete', 'key' => 'success_alert'],
            [
                'text'  => [
                    'en' => 'The user has been successfully deleted.',
                    'ja' => 'ユーザーは正常に削除されました。'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit_password', 'key' => 'heading'],
            [
                'text'  => [
                    'en' => 'Edit user password',
                    'ja' => 'ユーザーパスワードを編集する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit_password', 'key' => 'email_label'],
            [
                'text'  => [
                    'en' => 'Email',
                    'ja' => 'Eメール'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit_password', 'key' => 'password_label'],
            [
                'text'  => [
                    'en' => 'Password',
                    'ja' => 'パスワード'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit_password', 'key' => 'confirm_password_label'],
            [
                'text'  => [
                    'en' => 'Confirm Password',
                    'ja' => 'パスワードを認証する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit_password', 'key' => 'submit_button_name'],
            [
                'text'  => [
                    'en' => 'Change password',
                    'ja' => 'パスワードを変更する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'modal_user_edit_password', 'key' => 'success_alert'],
            [
                'text'  => [
                    'en' => 'Password has been changed.',
                    'ja' => 'パスワード変更済み。'
                ]
            ]
        );
        // end user index

        // begin uploaded file index
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'page_title'],
            [
                'text'  => [
                    'en' => 'Uploaded files',
                    'ja' => 'アップロードされたファイル'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'upload_button_name'],
            [
                'text'  => [
                    'en' => 'Upload',
                    'ja' => 'アップロードする'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'table_title'],
            [
                'text'  => [
                    'en' => 'Uploaded File List',
                    'ja' => 'アップロードされたファイル一覧'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'table_file_name_column_title'],
            [
                'text'  => [
                    'en' => 'File Name',
                    'ja' => 'ファイル名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'table_uploaded_at_column_title'],
            [
                'text'  => [
                    'en' => 'Uploaded At',
                    'ja' => 'アップロード日時'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'table_action_column_title'],
            [
                'text'  => [
                    'en' => 'Action',
                    'ja' => 'アクション'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'table_delete_button_name'],
            [
                'text'  => [
                    'en' => 'Delete',
                    'ja' => '削除する'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'modal_delete_heading'],
            [
                'text'  => [
                    'en' => 'Delete file',
                    'ja' => 'ファイルを削除する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'modal_delete_question'],
            [
                'text'  => [
                    'en' => 'Do you really want to delete this?',
                    'ja' => '本当に削除しますか？'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'modal_delete_button_name'],
            [
                'text'  => [
                    'en' => 'Delete',
                    'ja' => '削除する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'modal_delete_success_message'],
            [
                'text'  => [
                    'en' => 'Successfully deleted the file with data',
                    'ja' => 'データを含むファイルを正常に削除しました'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'upload_modal_heading'],
            [
                'text'  => [
                    'en' => 'File upload screen',
                    'ja' => 'ファイルアップロード画面'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'upload_modal_form_select_file_input_label'],
            [
                'text'  => [
                    'en' => 'Please select an Excel file to upload',
                    'ja' => 'アップロードするExcelファイルを選択してください'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'upload_modal_form_select_file_input_placeholder'],
            [
                'text'  => [
                    'en' => 'Choose a file',
                    'ja' => 'ファイルを選ぶ'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'upload_modal_form_submit_button_name'],
            [
                'text'  => [
                    'en' => 'Upload',
                    'ja' => 'アップロードする'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'import_failed_exception_error_alert'],
            [
                'text'  => [
                    'en' => 'Failed to import Excel data.',
                    'ja' => 'Excelデータのインポートに失敗しました。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'inport_failed_validation_error_alert'],
            [
                'text'  => [
                    'en' => 'Failed to import Excel data.',
                    'ja' => 'Excelデータのインポートに失敗しました。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'upload_modal_error_message'],
            [
                'text'  => [
                    'en' => 'Failed to import Excel data.',
                    'ja' => 'Excelデータのインポートに失敗しました。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'upload_modal_success_message'],
            [
                'text'  => [
                    'en' => 'Excel data has been successfully imported.',
                    'ja' => 'Excelデータは正常にインポートされました。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'unsupported_file_uploaded'],
            [
                'text'  => [
                    'en' => 'An unsupported file is selected. Please select an Excel or csv file.',
                    'ja' => '未対応のファイルが選択されています。Excelファイルかcsvファイルを選択してください。'
                ]
            ]
        );

        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'no_data_in_excel_file'],
            [
                'text'  => [
                    'en' => 'Start row is beyond the last row of data, or there is no data in the excel sheet.',
                    'ja' => '開始行以降にデータが存在しない、もしくは空のExcelシートが選択されています。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'uploaded_file_index', 'key' => 'unexpected_system_error'],
            [
                'text'  => [
                    'en' => 'An unexpected system error occurred. Please contact an system administrator.',
                    'ja' => '予期せぬシステムエラーが発生しました。システム管理者へお問い合わせください。',
                ]
            ]
        );
        // end uploaded file index

        // begin setting index
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'page_title'],
            [
                'text'  => [
                    'en' => 'Setting list',
                    'ja' => '設定リスト'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'reset_button_name'],
            [
                'text'  => [
                    'en' => 'Reset',
                    'ja' => 'リセット'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'submit_button_name'],
            [
                'text'  => [
                    'en' => 'Submit',
                    'ja' => '提出する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'date_display_name'],
            [
                'text'  => [
                    'en' => 'Date display',
                    'ja' => '日付表示'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'date_display_japanese_calendar_label'],
            [
                'text'  => [
                    'en' => 'Japanese Calendar',
                    'ja' => '和暦'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'date_display_christian_calendar_label'],
            [
                'text'  => [
                    'en' => 'Christian',
                    'ja' => '西暦'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'upload_file_name_display_name'],
            [
                'text'  => [
                    'en' => 'Upload file name display',
                    'ja' => 'アップロードファイル名表示'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'upload_file_name_display_display_label'],
            [
                'text'  => [
                    'en' => 'Display',
                    'ja' => '表示'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'upload_file_name_display_hidden_label'],
            [
                'text'  => [
                    'en' => 'Hidden',
                    'ja' => '非表示'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'initial_sort_order_name'],
            [
                'text'  => [
                    'en' => 'Initial sort order',
                    'ja' => '初期ソート順'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'initial_sort_order_ascending_order'],
            [
                'text'  => [
                    'en' => 'Ascending order',
                    'ja' => '昇順'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'initial_sort_order_descending_order'],
            [
                'text'  => [
                    'en' => 'Descending order',
                    'ja' => '降順'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'initial_display_number_name'],
            [
                'text'  => [
                    'en' => 'Initial display number',
                    'ja' => '初期表示件数'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'half_width_full_width_name'],
            [
                'text'  => [
                    'en' => 'Half-width / full-width',
                    'ja' => '半角／全角'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'half_width_full_width_distinguish_label'],
            [
                'text'  => [
                    'en' => 'Distinguish',
                    'ja' => '区別する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'half_width_full_width_do_not_distinguish_label'],
            [
                'text'  => [
                    'en' => 'Do not distinguish',
                    'ja' => '区別しない'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'numeric_values_truncate_or_round_name'],
            [
                'text'  => [
                    'en' => 'Numeric values truncate or round',
                    'ja' => '数値は切り捨てまたは丸めます'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'numeric_values_truncate_or_round_truncate_label'],
            [
                'text'  => [
                    'en' => 'Truncate',
                    'ja' => '切り捨て'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'numeric_values_truncate_or_round_round_label'],
            [
                'text'  => [
                    'en' => 'Round',
                    'ja' => '円形'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'numeric_values_decimal_places_label'],
            [
                'text'  => [
                    'en' => 'Numeric values decimal places',
                    'ja' => '小数点以下の数値'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'table_id_column_title'],
            [
                'text'  => [
                    'en' => 'ID',
                    'ja' => 'ID'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'table_name_column_title'],
            [
                'text'  => [
                    'en' => 'Name',
                    'ja' => '名'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'table_value_column_title'],
            [
                'text'  => [
                    'en' => 'Value',
                    'ja' => '値'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'modal_save_setting_heading'],
            [
                'text'  => [
                    'en' => 'Save settings',
                    'ja' => '設定を保存する'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'setting_index', 'key' => 'update_success_alert'],
            [
                'text'  => [
                    'en' => 'Your configuration has been successfully updated.',
                    'ja' => '設定は正常に更新されました。'
                ]
            ]
        );
        // end setting index

        // begin error message index
        LanguageLine::updateOrCreate(
            [ 'group' => 'error_message_index', 'key' => 'parameter_error' ],
            [
                'text'  => [
                    'en' => 'A parameter error has occurred.',
                    'ja' => 'パラメータエラーが発生しました。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            [ 'group' => 'error_message_index', 'key' => 'unsupported_parameter_error' ],
            [
                'text'  => [
                    'en' => 'An unsupported parameter has been set.',
                    'ja' => '未対応のパラメータが設定されました。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            [ 'group' => 'error_message_index', 'key' => 'resource_not_found' ],
            [
                'text'  => [
                    'en' => 'The specified resource was not found..',
                    'ja' => '指定されたリソースは見つかりませんでした。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            [ 'group' => 'error_message_index', 'key' => 'validation_error' ],
            [
                'text'  => [
                    'en' => 'There is incorrect data.',
                    'ja' => '不正なデータがあります。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            ['group' => 'error_message_index', 'key' => 'unexpected_system_error'],
            [
                'text'  => [
                    'en' => 'An unexpected system error occurred. Please contact an system administrator.',
                    'ja' => '予期せぬシステムエラーが発生しました。システム管理者へお問い合わせください。',
                ]
            ]
        );
        // end error message index

        // begin error message for error_details
        LanguageLine::updateOrCreate(
            [ 'group' => 'error_details_message', 'key' => 'unsupported_parameter_error' ],
            [
                'text'  => [
                    'en' => 'Not support with this parameter.',
                    'ja' => 'このパラメータには対応していません。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            [ 'group' => 'error_details_message', 'key' => 'file_does_not_exist' ],
            [
                'text'  => [
                    'en' => 'The specified file does not exist..',
                    'ja' => '指定されたファイルは存在しません。'
                ]
            ]
        );
        LanguageLine::updateOrCreate(
            [ 'group' => 'error_details_message', 'key' => 'stop_process_by_validation_error' ],
            [
                'text'  => [
                    'en' => 'Processing was interrupted because invalid data exceeded a certain number.',
                    'ja' => '不正なデータが一定数を超えたため、処理を中断しました。'
                ]
            ]
        );
        // end error message for error_details
    }
}
