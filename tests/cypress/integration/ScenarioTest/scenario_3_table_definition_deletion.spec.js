const dashboardURL = '/';
const postDefinitionUploadAPI = '/api/v1/definition-bulk';
const postFileUploadAPI = '/upload-excel';
const getTableAPI = '/api/v1/tables';
const getDatasourceAPI = '/api/get/data-source/all';
const getTableConfirmRelationAPI = '/api/v1/confirm-relation/tables**';
const postTableDeleteAPI = '/api/v1/delete/tables';
const postDatasourceDeleteAPI = '/api/delete/data-source';
const getDataSourceListAPI = '/api/v1/datasources';
const files = {
    path: 'OPU/',
    definitionUploadExcel: 'GENS定義情報設定シート_採卵・OPU.xlsx',
    fileUploadExcel: 'テストデータ_OPU記録台帳_101件.xlsx',
};
const selectors = {
    // ダッシュボードメニュー
    dashboardFileUpload: 'ファイルアップロード画面',
    dashboardDefinitionUpload: 'Definition Upload画面',
    dashboardTableLists: 'Table Lists画面',
    dashboardTableColumns: 'Table Columns画面',
    dashboardDatasource: 'Datasource画面',
    dashboardDatasourceColumns: 'Datasource Columns画面',
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
    // アップロード成功ポップアップ タイトル（ファイルアップロード画面）
    successPopUpTitle: '#vResCardTitle',
    // アップロード成功ポップアップ 詳細（ファイルアップロード画面）
    successPopUpDetail: '#successDialogText',
    // アップロード成功ポップアップ 閉じるボタン
    successPopUpCloseBtn: '#closeBtn',
    // アップロード失敗時のポップアップ詳細（ファイルアップロード画面）
    elsePopUpDetail: '#elseErrorDialogText',
    // シート選択解除ボタン
    clearSheetBtn: '#clearSheetBtn',

    // 「アップロードするファイル」プルダウン
    datasourceSelect: '#datasourceSelect',
    // 対象データソース
    targetDatasource: 'OPU記録台帳2',
    // 対象テーブル
    targetTable: 'xls_opu_kiroku_daicho2',

    // テーブル削除ボタン
    tableDeleteBtn: '.mdi.mdi-delete',
    // ダイアログ
    dialog: '.v-dialog.v-dialog--active',

    // 削除成功を表すメッセージ
    deleteSuccessMsg: 'Deleted Successfully!',
    // 紐づくデータソースがあるため削除できないという旨を表すメッセージ
    cannotDeleteBecauseOfDatasourceMsg: 'This table can not be deleted because datasource(s) depend on this table.',
    // 実テーブルがあるため削除できないという旨を表すメッセージ
    cannotDeleteBecauseOfRawDataMsg: 'This table can not be deleted because the raw data table has data.'
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
            .find("[value=OPU記録台帳2]")
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

describe('データソース定義情報の削除', function () {
    it('[ダッシュボード]より[Datasource画面] を起動し条件のデータソースを削除する。', function () {
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

    it('[ダッシュボード]より[Datasource Columns画面] を起動する。', function () {
        cy.server();

        // データソース取得API (waitのために設定しておく)
        cy.route("get", getDatasourceAPI).as('getDatasource');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDatasourceColumns)
            .click();

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
});

describe('テーブル定義情報の削除', function () {
    it('[ダッシュボード]より[Table Lists画面] を起動し条件の行の削除ボタンを押下する。', function () {
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
            cy.contains('OK').click({
                force: true
            });
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

    it('[ダッシュボード]より[Table Columns画面] を起動する。', function () {
        cy.server();

        // テーブルリスト取得API (waitのために設定しておく)
        cy.route("get", getTableAPI).as('getTable');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardTableColumns)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTable');

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
});

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
            .find("[value=OPU記録台帳2]")
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

describe('テーブル定義情報とデータソース定義情報の一括削除', function () {
    it('[ダッシュボード]より[Table Lists画面] を起動し条件の行の削除ボタンを押下する。', function () {
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
            cy.contains('OK').click({
                force: true
            });
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

    it('[ダッシュボード]より[Table Columns画面] を起動する。', function () {
        cy.server();

        // テーブルリスト取得API (waitのために設定しておく)
        cy.route("get", getTableAPI).as('getTable');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardTableColumns)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTable');

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

    it('[ダッシュボード]より[Datasource画面] を起動する。', function () {
        cy.server();

        // データソース削除API (waitのために設定しておく)
        cy.route("post", postDatasourceDeleteAPI).as('postDatasourceDelete');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDatasource)
            .click();

        cy.contains(selectors.targetDatasource)
            .should('not.exist');
    });

    it('[ダッシュボード]より[Datasource Columns画面] を起動する。', function () {
        cy.server();

        // データソース取得API (waitのために設定しておく)
        cy.route("get", getDatasourceAPI).as('getDatasource');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardDatasourceColumns)
            .click();

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
});

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
            .find("[value=OPU記録台帳2]")
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

describe('テーブルへのデータアップロード', function () {
    it('[ダッシュボード]より[ファイルアップロード画面] を起動し条件のデータを取り込む。 ', function () {
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

        // 「アップロードするファイル」のドロップダウンリストの対象の項目を選択
        cy.contains(selectors.targetDatasource)
            .click();

        // ファイルを選択
        cy.get(selectors.dAndDArea).attachFile(files.path + '1/' + files.fileUploadExcel);

        // 対象のシートをクリック
        cy.get('.sheetBtns')
            .find("[value=101件]")
            .click();

        // 送信ボタンをクリック
        cy.get(selectors.submitBtn)
            .click();

        // response を受け取るまで待つ
        cy.wait('@postFileUpload', {
            timeout: 120000
        });

        // アップロード成功のポップアップが表示されること
        cy.get(selectors.successPopUp)
            .should('be.visible');
        cy.get(selectors.successPopUpTitle)
            .should('have.text', 'アップロード成功');

        // 設定したファイル名とシート名が表示されていること。
        cy.get(selectors.successPopUpDetail)
            .should('include.text', `ファイル名: ${files.fileUploadExcel}`)
            .should('include.text', 'シート名: 101件')
            .should('include.text', 'アップロード形式: 追加');

        // Closeボタンをクリック
        cy.get(selectors.successPopUpCloseBtn)
            .click();
    });
});

describe('テーブル定義情報の削除ができないことを確認', function () {
    it('[ダッシュボード]より[Table Lists画面] を起動し条件の行の削除ボタンを押下する。', function () {
        cy.server();

        // テーブル関係確認API (waitのために設定しておく)
        cy.route("get", getTableConfirmRelationAPI).as('getTableConfirmRelation');

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

        cy.contains(selectors.cannotDeleteBecauseOfRawDataMsg)
            .should('exist');

        cy.contains(selectors.targetTable)
            .should('exist');
    });

    it('[ダッシュボード]より[Table Columns画面] を起動する。', function () {
        cy.server();

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardTableColumns)
            .click();

        cy.contains(selectors.targetTable)
            .should('exist');
    });
});
