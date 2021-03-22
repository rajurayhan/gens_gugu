const dashboardURL = '/';
const tableListURL = '/admin/tables';
const tabledataURL = '/tabledata'
const getTableDataAPI = '/api/v1/table-data/*';
const tableColumnListsURL = '/admin/list';
// const datasourceURL = '/admin/datasource';
const datasourceColumnsURL = '/admin/datasource-columns';

const postDefinitionUploadAPI = '/api/v1/definition-bulk';
const postFileUploadAPI = '/upload-excel';

const getFileListAPI = '/api/v1/excel_files';
const getTableListAPI = '/api/v1/tables';
const getTableColumnsAPI = '/api/get/table-columns/*';
const getDatasourceAPI = '/api/get/data-source/all';
const getDatasourceColumnsAPI = '/api/get/datasource-columns';
const postTableAddAPI = '/api/v1/add/tables';
const postTableColumnAddAPI = '/api/v1/add/table-columns';
const postDatasourceAddAPI = '/api/add/data-source';
const postDatasourceColumnAddAPI = '/api/add/datasource-columns';
const getTableConfirmRelationAPI = '/api/v1/confirm-relation/tables**';
const postTableDeleteAPI = '/api/v1/delete/tables';
const postTableColumnDeleteAPI = '/api//v1/delete/table-columns';
const postDatasourceDeleteAPI = '/api/delete/data-source';
const postDatasourceColumnDeleteAPI = '/api/delete/datasource-columns';
const getDataSourceListAPI = '/api/v1/datasources';
const files = {
    path: 'OPU/',
    definitionUploadExcel: 'GENS定義情報設定シート_採卵・OPU.xlsx',
};
const selectors = {
    // ダッシュボードメニュー
    dashboardFileUpload: 'ファイルアップロード画面',
    dashboardFileList: 'ファイル一覧画面',
    dashboardUploadedData: 'アップロードデータ表示画面',
    dashboardDefinitionUpload: 'Definition Upload画面',
    dashboardTableLists: 'Table Lists画面',
    dashboardTableColumns: 'Table Columns画面',
    dashboardDatasource: 'Datasource画面',
    dashboardDatasourceColumns: 'Datasource Columns画面',
    // 左ペインを開くボタン
    drawerButton: '.v-app-bar__nav-icon',
    // 左ペイン
    navigationDrawer: '.v-navigation-drawer',

    // D&Dエリア
    dAndDArea: '#file',
    // プレビューエリア
    previewArea: '#exceltable',
    // シートボタン
    sheetBtn: '.sheetBtns',
    // 送信ボタン
    submitBtn: '#submitBtn',
    // アップロード成功ポップアップ
    successPopUp: '.v-dialog',
    // アップロード成功ポップアップ タイトル（定義情報アップロード画面）
    definitionUploadSuccessPopUpTitle: '#vCardTitle',
    // アップロード成功ポップアップ 詳細（定義情報アップロード画面）
    definitionUploadSuccessPopUpDetail: '#successDialog',

    // New Itemボタン
    newItemBtn: 'button:contains("New Item")',
    // New Itemポップアップ
    newItemPopup: '.v-dialog:contains("New Item")',
    // Saveボタン
    saveButton: 'button:contains("Save")',
    // 「アップロードするファイル」プルダウン
    datasourceSelect: '#datasourceSelect',

    // 対象
    targetTable: 'xls_sairan_seiseki_daicho_ishikawa',
    targetTableAlias: '採卵成績台帳Ishikawa',
    targetTableColumn: '採卵間隔',
    targetDatasource: '採卵成績台帳Ishikawa',
    targetDatasourceColumn: 'コメント',

    // テーブル削除ボタン
    tableDeleteBtn: '.mdi.mdi-delete',
    // ダイアログ
    dialog: '.v-dialog.v-dialog--active',

    // 追加成功を表すメッセージ
    CreateSuccessMsg: 'Created Successfully!',
    // 削除成功を表すメッセージ
    deleteSuccessMsg: 'Deleted Successfully!',
};

describe('テーブル初期登録', function () {
    it('[ダッシュボード]より[Definition Upload画面] を起動し条件のデータを取り込む。', function () {
        cy.server();

        // 定義情報一括アップロードAPI (waitのために設定しておく)
        cy.route("post", postDefinitionUploadAPI).as('postDefinitionUpload');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDefinitionUpload)
            .click();

        // ファイルを選択
        cy.get(selectors.dAndDArea).attachFile(files.path + files.definitionUploadExcel);

        // ここではプレビューが表示されていることの確認のみ
        cy.get(selectors.previewArea)
            .should('be.visible');

        // 対象のシートをクリック
        cy.get(selectors.sheetBtn)
            .find("[value=採卵成績台帳Ishikawa]")
            .click();

        // 送信をクリック
        cy.get(selectors.submitBtn)
            .click();

        // response を受け取るまで待つ
        cy.wait('@postDefinitionUpload', {
            timeout: 60000
        });

        // 定義設定成功のポップアップが表示されること
        cy.get(selectors.successPopUp)
            .should('be.visible');
        cy.get(selectors.definitionUploadSuccessPopUpTitle)
            .should('have.text', '定義情報の設定成功');

        // 設定したデータソース名とテーブル名が表示されていること。
        cy.get(selectors.definitionUploadSuccessPopUpDetail)
            .should('include.text', `データソース名: ${selectors.targetDatasource}`)
            .should('include.text', `テーブル名: ${selectors.targetTable}`);
    });
});

describe('データソースカラムの削除と表示の確認', function () {
    it('[ダッシュボード]より[Datasource Columns画面] を起動し、該当行の削除ボタンを押下する。', function () {
        cy.server();

        // データソースカラム削除API (waitのために設定しておく)
        cy.route("post", postDatasourceColumnDeleteAPI).as('postDatasourceColumnDelete');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDatasourceColumns)
            .click();

        cy.contains(selectors.targetDatasourceColumn).parents('tr').within(() => {
            cy.get(selectors.tableDeleteBtn)
                .click();
        })

        cy.on('window:confirm', () => true);

        // response を受け取るまで待つ
        cy.wait('@postDatasourceColumnDelete', {
            timeout: 60000
        });

        cy.contains(selectors.deleteSuccessMsg)
            .should('exist');

        cy.contains(selectors.targetDatasourceColumn)
            .should('not.exist');
    });
});

describe('データソースカラムの再追加', function () {
    it('New Item ボタンを押下し、削除したデータソースカラム情報と同じ内容を入力してSaveボタンを押下する', function () {
        cy.server();

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDatasourceColumns)
            .click();

        // テーブルカラム取得API (waitのために設定しておく)
        cy.route("get", getTableColumnsAPI).as('getTableColumns');
        // データソースカラム追加API (waitのために設定しておく)
        cy.route("post", postDatasourceColumnAddAPI).as('postDatasourceColumnAdd');

        cy.get(selectors.newItemBtn).click();

        cy.get(selectors.newItemPopup)
            .should('be.visible');

        //Input values
        const values = {
            datasource_name: selectors.targetDatasource,
            column_index: 'BA',
            column_name: selectors.targetDatasourceColumn,
            table_column: 'comment'

        }
        //Datasource Name
        cy.get(selectors.newItemPopup)
            .find('label:contains("Datasource Name")').parents('div[role="button"]')
            .click();
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(values.datasource_name)
            .click();


        //Column Index
        cy.get(selectors.newItemPopup)
            .find('label:contains("Column Index")').siblings('input')
            .type(values.column_index);

        //Column Name
        cy.get(selectors.newItemPopup)
            .find('label:contains("Column Name")').siblings('input')
            .type(values.column_name);

        //Table Column
        cy.get(selectors.newItemPopup)
            .find('label:contains("Table Column")').parents('div[role="button"]')
            .click();
        cy.wait('@getTableColumns', {
            timeout: 20000
        });
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(values.table_column)
            .click();

        //save
        cy.get(selectors.saveButton).click();

        // response を受け取るまで待つ
        cy.wait('@postDatasourceColumnAdd', {
            timeout: 60000
        });

        cy.contains(selectors.CreateSuccessMsg)
            .should('exist');

        cy.contains(selectors.targetDatasourceColumn)
            .should('exist');
    });
});

describe('データソースの削除と表示の確認', function () {
    it('[ダッシュボード]より[Datasource画面] を起動し該当行の削除ボタンを押下する。', function () {
        cy.server();

        // データソース削除API (waitのために設定しておく)
        cy.route("post", postDatasourceDeleteAPI).as('postDatasourceDelete');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDatasource)
            .click();

        cy.contains(selectors.targetDatasource).parents('tr').within(() => {
            cy.get(selectors.tableDeleteBtn)
                .click();
        })

        cy.on('window:confirm', () => true);

        // response を受け取るまで待つ
        cy.wait('@postDatasourceDelete', {
            timeout: 60000
        });

        cy.contains(selectors.deleteSuccessMsg)
            .should('exist');

        cy.contains(selectors.targetDatasource)
            .should('not.exist');
    });

    it('左ペインより [Datasource Columns]のメニューをクリックする。', function () {
        cy.server();

        // データソース取得API (waitのために設定しておく)
        cy.route("get", getDatasourceAPI).as('getDatasource');

        //左ペインを開く
        cy.get(selectors.drawerButton).click();

        cy.get('a[href="' + datasourceColumnsURL + '"] > .v-list-item').click();

        // response を受け取るまで待つ
        cy.wait('@getDatasource');

        // 「Datasource Name」リストにないこと
        cy.get('label:contains("Datasource Name")').parents('div[role="button"]')
            .click();
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(selectors.targetDatasource)
            .should('not.exist');
    });

    it('[ダッシュボード]より[ファイルアップロード画面] を起動する。 ', function () {
        cy.server();

        // datasource取得API（waitのために設定しておく）
        cy.route("get", getDataSourceListAPI).as('getDataSourceList');

        // ファイルアップロードAPI（waitのために設定しておく）
        cy.route("post", postFileUploadAPI).as('postFileUpload');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardFileUpload)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getDataSourceList');

        // 「アップロードするファイル」のドロップダウンリストから選択
        cy.get(selectors.datasourceSelect)
            .click({
                force: true
            });
        // 「アップロードするファイル」のドロップダウンリストにないこと
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(selectors.targetDatasource)
            .should('not.exist');
    });

    it('[ダッシュボード]より[ファイル一覧画面] を起動する。', function () {
        cy.server();

        // ファイル一覧取得API（waitのために設定しておく）
        cy.route("get", getFileListAPI).as('getFileList');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardFileList)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getFileList');

        cy.get('header').contains('ファイル一覧')
            .should('exist');

        cy.contains('システムエラーが発生しました。IT部門に連絡してください')
            .should('not.visible');
    });
});

describe('データソースの再追加', function () {
    it('[ダッシュボード]より[Datasource画面] を起動し、New Itemボタンを押下してから削除したデータソースと同じ情報を入力してSaveボタンを押下する', function () {
        cy.server();

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDatasource)
            .click();

        // データソース追加API (waitのために設定しておく)
        cy.route("post", postDatasourceAddAPI).as('postDatasourceAdd');

        cy.get(selectors.newItemBtn).click();

        cy.get(selectors.newItemPopup)
            .should('be.visible');

        //Input values
        const values = {
            source_name: selectors.targetDatasource,
            table_id: selectors.targetTable,
            starting_row_number: '4'

        }
        //Source Name
        cy.get(selectors.newItemPopup)
            .find('label:contains("Source Name")').siblings('input')
            .type(values.source_name);

        //Table Id
        cy.get(selectors.newItemPopup)
            .find('label:contains("Table Id")').parents('div[role="button"]')
            .click();
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(values.table_id)
            .click();

        //Starting Row Number
        cy.get(selectors.newItemPopup)
            .find('label:contains("Starting Row Number")').siblings('input')
            .type(values.starting_row_number);

        //save
        cy.get(selectors.saveButton).click();

        // response を受け取るまで待つ
        cy.wait('@postDatasourceAdd', {
            timeout: 60000
        });

        cy.contains(selectors.CreateSuccessMsg)
            .should('exist');

        cy.contains(selectors.targetDatasource)
            .should('exist');
    });

    it('左ペインより [Datasource Columns]のメニューをクリックする。', function () {
        cy.server();

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDatasource)
            .click();

        // データソース取得API (waitのために設定しておく)
        cy.route("get", getDatasourceAPI).as('getDatasource');
        // データソースカラム取得API
        cy.route("get", getDatasourceColumnsAPI).as('getDatasourceColumns');

        //左ペインを開く
        cy.get(selectors.drawerButton).click();

        cy.get('a[href="' + datasourceColumnsURL + '"] > .v-list-item').click();

        // response を受け取るまで待つ
        cy.wait('@getDatasource');
        cy.wait('@getDatasourceColumns');

        // 「Datasource Name」リストにあること
        cy.get('label:contains("Datasource Name")').parents('div[role="button"]')
            .click();
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(selectors.targetDatasource)
            .should('exist');
    });

    it('[ダッシュボード]より[ファイルアップロード画面] を起動する。 ', function () {
        cy.server();

        // datasource取得API（waitのために設定しておく）
        cy.route("get", getDataSourceListAPI).as('getDataSourceList');

        // ファイルアップロードAPI（waitのために設定しておく）
        cy.route("post", postFileUploadAPI).as('postFileUpload');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardFileUpload)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getDataSourceList');

        // 「アップロードするファイル」のドロップダウンリストから選択
        cy.get(selectors.datasourceSelect)
            .click({
                force: true
            });
        // 「アップロードするファイル」のドロップダウンリストにあること
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')

            .contains(selectors.targetDatasource)
            .should('exist');
    });

    it('[ダッシュボード]より[ファイル一覧画面] を起動する。', function () {
        cy.server();

        // ファイル一覧取得API（waitのために設定しておく）
        cy.route("get", getFileListAPI).as('getFileList');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardFileList)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getFileList');

        cy.get('header').contains('ファイル一覧')
            .should('exist');

        cy.contains('システムエラーが発生しました。IT部門に連絡してください')
            .should('not.visible');
    });
});


describe('テーブルカラムの削除', function () {
    it('[ダッシュボード]より[Table Columns画面] を起動し、該当行の削除ボタンを押下する。', function () {
        cy.server();

        // テーブルカラム削除API (waitのために設定しておく)
        cy.route("post", postTableColumnDeleteAPI).as('postTableColumnDelete');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardTableColumns)
            .click();

        cy.contains(selectors.targetTableColumn).parents('tr').within(() => {
            cy.get(selectors.tableDeleteBtn)
                .click();
        })

        cy.on('window:confirm', () => true);

        // response を受け取るまで待つ
        cy.wait('@postTableColumnDelete', {
            timeout: 60000
        });

        cy.contains(selectors.deleteSuccessMsg)
            .should('exist');

        cy.contains(selectors.targetTableColumn)
            .should('not.exist');
    });

    it('[ダッシュボード]より[アップロードデータ表示画面] を起動する。', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardUploadedData)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 対象のテーブル名が存在すること
        cy.get(selectors.navigationDrawer).within(() => {
            cy.contains(selectors.targetTableAlias)
                .should('exist');
        });

    });

    it('該当のテーブル定義名をクリックし画面へ遷移する', function () {
        cy.server();

        // アップロードデータ取得API（waitのために設定しておく）
        cy.route("get", getTableDataAPI).as('getTableData');


        cy.get(selectors.navigationDrawer).within(() => {
            cy.contains(selectors.targetTableAlias).click({
                force: true
            });
        });

        // response を受け取るまで待つ
        cy.wait('@getTableData');

        cy.get('table > thead > tr').within(() => {
            cy.contains(selectors.targetTableColumn)
                .should('not.exist');
        });
    });
});



describe('テーブルカラム定義の再追加', function () {
    it('[ダッシュボード]より[Table Column画面] を起動し、New Itemボタンを押下してから削除したテーブルカラムと同じ情報を入力してSaveボタンを押下する', function () {
        cy.server();

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardTableColumns)
            .click();

        // テーブルカラム追加API (waitのために設定しておく)
        cy.route("post", postTableColumnAddAPI).as('postTableColumnAdd');

        cy.get(selectors.newItemBtn).click();

        cy.get(selectors.newItemPopup)
            .should('be.visible');

        //Input values
        const values = {
            table_name: selectors.targetTable,
            column_name: 'sairan_kankaku',
            column_name_alias: '採卵間隔',
            data_type: 'BIGINT',
            length: 10,
        };

        //Table Name
        cy.get(selectors.newItemPopup)
            .find('label:contains("Table Name")').parents('div[role="button"]')
            .click();
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(values.table_name)
            .click();

        //Column Name
        cy.get(selectors.newItemPopup)
            .find('label:contains("Column Name")').first().siblings('input')
            .type(values.column_name);

        //Column Name Alias
        cy.get(selectors.newItemPopup)
            .find('label:contains("Column Name Alias")').siblings('input')
            .type(values.column_name_alias);

        // Data Type
        cy.get(selectors.newItemPopup)
            .find('label:contains("Data Type")').parents('div[role="button"]')
            .click();
        cy.get('.menuable__content__active')　//Not need to scroll for data_type
            .contains(values.data_type)
            .click();

        //Length
        cy.get(selectors.newItemPopup)
            .find('label:contains("Length")').siblings('input')
            .clear()
            .type(values.length);

        //save
        cy.get(selectors.saveButton).click();

        // response を受け取るまで待つ
        cy.wait('@postTableColumnAdd', {
            timeout: 60000
        });

        cy.contains(selectors.CreateSuccessMsg)
            .should('exist');

        cy.contains(selectors.targetTableColumn)
            .should('exist');
    });

    it('[ダッシュボード]より[アップロードデータ表示画面] を起動する。', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardUploadedData)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 対象のテーブル名が存在すること
        cy.get(selectors.navigationDrawer).within(() => {
            cy.contains(selectors.targetTableAlias)
                .should('exist');
        });

    });

    it('該当のテーブル定義名をクリックし画面へ遷移する', function () {
        cy.server();

        // アップロードデータ取得API（waitのために設定しておく）
        cy.route("get", getTableDataAPI).as('getTableData');


        cy.get(selectors.navigationDrawer).within(() => {
            cy.contains(selectors.targetTableAlias).click({
                force: true
            });
        });

        // response を受け取るまで待つ
        cy.wait('@getTableData');

        cy.get('table > thead > tr').within(() => {
            cy.contains(selectors.targetTableColumn)
                .should('exist');
        });
    });
});


describe('テーブル定義の削除', function () {
    it('[ダッシュボード]より[Table Lists画面] を起動し該当行の削除ボタンを押下する。', function () {
        cy.server();

        // テーブル関係確認API (waitのために設定しておく)
        cy.route("get", getTableConfirmRelationAPI).as('getTableConfirmRelation');
        // テーブル削除API (waitのために設定しておく)
        cy.route("post", postTableDeleteAPI).as('postTableDelete');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardTableLists)
            .click();

        cy.contains(selectors.targetTable).parents('tr').within(() => {
            cy.get(selectors.tableDeleteBtn)
                .click();
        })

        cy.on('window:confirm', () => true);

        // response を受け取るまで待つ
        cy.wait('@getTableConfirmRelation', {
            timeout: 60000
        });

        cy.get(selectors.dialog).within(() => {
            cy.get('button.v-btn').contains('OK').click();
        });

        // response を受け取るまで待つ
        cy.wait('@postTableDelete', {
            timeout: 60000
        });

        cy.contains(selectors.deleteSuccessMsg)
            .should('exist');

        cy.contains(selectors.targetTable)
            .should('not.exist');
    });

    it('左ペインより [Table Columns]のメニューをクリックする。', function () {
        cy.server();

        // テーブルリスト取得API (waitのために設定しておく)
        cy.route("get", getTableListAPI).as('getTableList');

        //左ペインを開く
        cy.get(selectors.drawerButton).click();

        cy.get('a[href="' + tableColumnListsURL + '"] > .v-list-item').click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 「Table List」リストにないこと
        cy.get('label:contains("Table List")').parents('div[role="button"]')
            .click();
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(selectors.targetTable)
            .should('not.exist');
    });

    it('[ダッシュボード]より[ファイル一覧画面] を起動する。', function () {
        cy.server();

        // ファイル一覧取得API（waitのために設定しておく）
        cy.route("get", getFileListAPI).as('getFileList');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardFileList)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getFileList');

        cy.get('header').contains('ファイル一覧')
            .should('exist');

        cy.contains('システムエラーが発生しました。IT部門に連絡してください')
            .should('not.visible');
    });

    it('[ダッシュボード]より[アップロードデータ表示画面] を起動する。', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardUploadedData)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 対象のテーブル名が存在しないこと
        cy.get(selectors.navigationDrawer).within(() => {
            cy.contains(selectors.targetTableAlias)
                .should('not.exist');
        });
    });
});

describe('テーブル定義の再追加', function () {
    it('[ダッシュボード]より[Table Lists画面] を起動しNew Itemボタンを押下してから削除したテーブル定義と同じ情報を入力してSaveボタンを押下する', function () {
        cy.server();

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardTableLists)
            .click();

        // データソース追加API (waitのために設定しておく)
        cy.route("post", postTableAddAPI).as('postTableAdd');

        cy.get(selectors.newItemBtn).click();

        cy.get(selectors.newItemPopup)
            .should('be.visible');

        //Input values
        const values = {
            table_name: selectors.targetTable,
            table_name_alias: '採卵成績台帳Ishikawa',
        }

        //Table Name
        cy.get(selectors.newItemPopup)
            .find('label:contains("Table Name")').first().siblings('input')
            .type(values.table_name);

        //Table Name Alias
        cy.get(selectors.newItemPopup)
            .find('label:contains("Table Name Alias")').siblings('input')
            .type(values.table_name_alias);

        //save
        cy.get(selectors.saveButton).click();

        // response を受け取るまで待つ
        cy.wait('@postTableAdd', {
            timeout: 60000
        });

        cy.contains(selectors.CreateSuccessMsg)
            .should('exist');

        cy.contains(selectors.targetTable)
            .should('exist');
    });

    it('左ペインより [Table Columns]のメニューをクリックする。', function () {
        cy.server();

        // テーブルリスト取得API (waitのために設定しておく)
        cy.route("get", getTableListAPI).as('getTableList');

        //左ペインを開く
        cy.get(selectors.drawerButton).click();

        cy.get('a[href="' + tableColumnListsURL + '"] > .v-list-item').click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 「Table List」リストにあること
        cy.get('label:contains("Table List")').parents('div[role="button"]')
            .click();
        cy.get('.menuable__content__active >> .v-list-item').then(list => {
            const listCount = Cypress.$(list).length;
            if (listCount > 6) {
                //need scroll down depends of list box item's count
                cy.get('.menuable__content__active').scrollTo('bottom')
            }
        });
        cy.get('.menuable__content__active')
            .contains(selectors.targetTable)
            .should('exist');
    });

    it('[ダッシュボード]より[ファイル一覧画面] を起動する。', function () {
        cy.server();

        // ファイル一覧取得API（waitのために設定しておく）
        cy.route("get", getFileListAPI).as('getFileList');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardFileList)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getFileList');

        cy.get('header').contains('ファイル一覧')
            .should('exist');

        cy.contains('システムエラーが発生しました。IT部門に連絡してください')
            .should('not.visible');
    });

    it('[ダッシュボード]より[アップロードデータ表示画面] を起動する。', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardUploadedData)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 対象のテーブル名が存在すること
        cy.get(selectors.navigationDrawer).within(() => {
            cy.contains(selectors.targetTableAlias)
                .should('exist');
        });
    });

    it('該当のテーブル定義名をクリックし画面へ遷移する', function () {
        cy.server();

        // アップロードデータ取得API（waitのために設定しておく）
        cy.route("get", getTableDataAPI).as('getTableData');


        cy.get(selectors.navigationDrawer).within(() => {
            cy.contains(selectors.targetTableAlias).click({
                force: true
            });
        });

        // response を受け取るまで待つ
        cy.wait('@getTableData');

        // There is no columns yet
        // cy.get('table > thead > tr').within(() => {
        //     cy.contains(selectors.targetTableColumn)
        //         .should('exist');
        // });

        //エラーでないこと
        cy.contains('No data available')
            .should('visible');
    });
});
