const dashboardURL = '/';
const postDefinitionUploadAPI = '/api/v1/definition-bulk';
const postFileUploadAPI = '/upload-excel';
const getDataSourceListAPI = '/api/v1/datasources';
const getFileListAPI = '/api/v1/excel_files';
const getTableListAPI = '/api/v1/tables';
const getTableDataAPI = '/api/v1/table-data/*';
// vuetify(?)により左ペイン表示の際に半角スペースが挿入されるので付けておく
const testTargetTableNameAlias = ' OPU記録台帳';
const files = {
    path: 'OPU/',
    definitionUploadExcel: 'GENS定義情報設定シート_採卵・OPU.xlsx',
    fileUploadExcel: 'テストデータ_OPU記録台帳_101件.xlsx',
    fileUploadExcel2: 'テストデータ_OPU記録台帳_追加.xlsx',
};
const selectors = {
    // ダッシュボードメニュー
    dashboardFileUpload: 'ファイルアップロード画面',
    dashboardFileList: 'ファイル一覧画面',
    dashboardUploadedData: 'アップロードデータ表示画面',
    dashboardDefinitionUpload: 'Definition Upload画面',
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
    targetDatasource: 'OPU記録台帳',
    // 対象データソース2
    targetDatasource2: 'OPU記録台帳_追加',
    // 「開始行」テキストボックス
    startrow: '#startrow',
    // 「開始行」テキストボックス バリデーション
    startrowValidation: '.col-2 .v-messages__message',
    // アップロード形式「追加」ラジオボタン
    modeAppend: '#modeAppend',
    // アップロード形式「洗い替え」ラジオボタン
    modeReplace: '#modeReplace',
    // 左ペイン
    navigationDrawer: '#navidationDrawer',
    // データ表示エリア（data-table）
    dataTableUploadedData: '#dataTableUploadedData',
    // データ表示エリアのフッター
    dataTableFooter: '.v-data-footer',
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
            .find("[value=OPU記録台帳]")
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
            .should('include.text', 'テーブル名: xls_opu_kiroku_daicho');
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

describe('アップロード確認（ファイル一覧画面） ', function () {
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

        // ファイル一覧の最後に、アップロードしたファイルが表示されていること
        cy.get('tbody>tr')
            .eq(0)
            .contains(`${files.fileUploadExcel}`).should('exist');
    });
});

describe('データ詳細確認（Table Data画面） ', function () {
    it('[ダッシュボード]より[アップロードデータ表示画面] を起動する。', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        // アップロードデータ取得API（waitのために設定しておく）
        cy.route("get", getTableDataAPI).as('getTableData');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardUploadedData)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 対象のテーブルを選択
        // 左ペインのaタグのリストを取得
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, $list) => {
                // 対象のテーブル名を探す
                if ($el.text() == testTargetTableNameAlias) {
                    // 対象のテーブル名を見つけたら、要素をクリックする
                    cy.wrap($el).find('.v-list-item__title').click();
                }
            })

        // response を受け取るまで待つ
        cy.wait('@getTableData');

    });
    it('[OPU記録台帳]をクリックし画面へ遷移した後の確認', function () {

        // 1ページに表示するデータ数を選択するためのselectorが表示されていること
        cy.get(selectors.dataTableFooter).first()
            .should('be.visible');

        // data-tableに100件のデータが表示されていること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tbody').find('tr')
            .should('have.length', 100);

        // data-tableに今回アップロードしたデータが１ページ目にあること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tbody').find('tr')
            .contains('A001')
            .should('exist');
    });
});



describe('テーブルへのデータアップロード（洗い替え）', function () {
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
        cy.get(selectors.dAndDArea).attachFile(files.path + '2/' + files.fileUploadExcel);

        // 対象のシートをクリック
        cy.get('.sheetBtns')
            .find("[value=101件]")
            .click();

        // アップロード形式：洗い替えを選択
        cy.get(selectors.modeReplace).parents('.v-radio')
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
            .should('include.text', 'アップロード形式: 洗い替え');

        // Closeボタンをクリック
        cy.get(selectors.successPopUpCloseBtn)
            .click();
    });
});

describe('アップロード確認（ファイル一覧画面）（洗い替え） ', function () {
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

        // ファイル一覧にアップロードしたファイルが１つだけ表示されていること
        cy.get('tbody>tr')
            .contains(`${files.fileUploadExcel}`).should('have.length', 1)
    });
});

describe('データ詳細確認（Table Data画面）（洗い替え）', function () {
    it('[ダッシュボード]より[アップロードデータ表示画面] を起動する。', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        // アップロードデータ取得API（waitのために設定しておく）
        cy.route("get", getTableDataAPI).as('getTableData');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardUploadedData)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 対象のテーブルを選択
        // 左ペインのaタグのリストを取得
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, $list) => {
                // 対象のテーブル名を探す
                if ($el.text() == testTargetTableNameAlias) {
                    // 対象のテーブル名を見つけたら、要素をクリックする
                    cy.wrap($el).find('.v-list-item__title').click();
                }
            })

        // response を受け取るまで待つ
        cy.wait('@getTableData');

    });
    it('[OPU記録台帳]をクリックし画面へ遷移した後の確認', function () {

        // 1ページに表示するデータ数を選択するためのselectorが表示されていること
        cy.get(selectors.dataTableFooter).first()
            .should('be.visible');

        // data-tableに100件のデータが表示されていること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tbody').find('tr')
            .should('have.length', 100);

        // data-tableに今回アップロードしたデータが１ページ目にあること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tbody').find('tr')
            .contains('B001')
            .should('exist');
    });
});





describe('テーブルへのデータアップロード（洗い替え：準正常）', function () {
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
        cy.get(selectors.dAndDArea).attachFile(files.path + '3/' + files.fileUploadExcel);

        // 対象のシートをクリック
        cy.get('.sheetBtns')
            .find("[value=101件]")
            .click();

        // アップロード形式：追加を選択
        cy.get(selectors.modeAppend).parents('.v-radio')
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

    it('選択したファイルを解除してから再度条件のデータを取り込む', function () {
        cy.server();
        // ファイルアップロードAPI（waitのために設定しておく）
        cy.route("post", postFileUploadAPI).as('postFileUpload');

        // ダッシュボードより対象ページに移動
        // CypressがテストケースごとにCSRF Cookieを削除しているため、
        // CSRFトークンエラーとならないように、ファイルアップロード画面へアクセスしなおす
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardFileUpload)
            .click();

        // 「アップロードするファイル」のドロップダウンリストから選択
        cy.get(selectors.datasourceSelect)
            .click({
                force: true
            });

        // 「アップロードするファイル」のドロップダウンリストの対象の項目を選択
        cy.contains(selectors.targetDatasource)
            .click();

        // ファイルを選択
        cy.get(selectors.dAndDArea).attachFile(files.path + '4/' + files.fileUploadExcel);

        // 対象のシートをクリック
        cy.get('.sheetBtns')
            .find("[value=101件]")
            .click();

        // アップロード形式：洗い替えを選択
        cy.get(selectors.modeReplace).parents('.v-radio')
            .click();

        // 送信ボタンをクリック
        cy.get(selectors.submitBtn)
            .click();

        // response を受け取るまで待つ
        cy.wait('@postFileUpload', {
            timeout: 120000
        });

        // アラートが表示されること
        cy.get(selectors.successPopUp)
            .should('be.visible');
        cy.get(selectors.successPopUpTitle)
            .should('have.text', 'おっと！');
        cy.get(selectors.elsePopUpDetail)
            .contains('ファイル名とシート名が同じデータがすでに複数アップロードされているため、洗い替えできません。すでにアップロードされているデータを確認するか、ファイルを見直してください。');

        // Closeボタンをクリック
        cy.get(selectors.successPopUpCloseBtn)
            .click();
    });
});


describe('データソースのみの追加', function () {
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
            .find("[value=OPU記録台帳_追加]")
            .click();

        // 送信をクリック
        cy.get(selectors.submitBtn)
            .click();

        // response を受け取るまで待つ
        cy.wait('@postDefinitionUpload', {
            timeout: 60000
        });

        // 追加確認のポップアップが表示されること
        cy.get('.v-dialog')
            .should('be.visible');
        cy.get('#confirmDialogTitle')
            .should('have.text', 'Attention');

        // 設定したデータソース名とテーブル名が表示されていること。
        cy.get('#confirmDialogText')
            .should('include.text', 'xls_opu_kiroku_daicho')
            .should('include.text', selectors.targetDatasource);


        // ポップアップのOKをクリック
        cy.get('#confirmDialogOK').click();

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
            .should('include.text', `データソース名: ${selectors.targetDatasource2}`)
            .should('include.text', 'テーブル名: xls_opu_kiroku_daicho');
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
        cy.contains(selectors.targetDatasource2)
            .click();

        // ファイルを選択
        cy.get(selectors.dAndDArea).attachFile(files.path + '5/' + files.fileUploadExcel2);

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
            .should('include.text', `ファイル名: ${files.fileUploadExcel2}`)
            .should('include.text', 'シート名: 101件')
            .should('include.text', 'アップロード形式: 追加');

        // Closeボタンをクリック
        cy.get(selectors.successPopUpCloseBtn)
            .click();
    });
});

describe('アップロード確認（ファイル一覧画面） ', function () {
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

        // ファイル一覧の最後に、アップロードしたファイルが表示されていること
        cy.get('tbody>tr')
            .eq(0)
            .contains(`${files.fileUploadExcel2}`).should('exist');
    });
});

describe('データ詳細確認（Table Data画面） ', function () {
    it('[ダッシュボード]より[アップロードデータ表示画面] を起動する。', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        // アップロードデータ取得API（waitのために設定しておく）
        cy.route("get", getTableDataAPI).as('getTableData');

        // ダッシュボードより対象ページに移動
        cy.visit(dashboardURL);
        cy.get('.container').contains(selectors.dashboardUploadedData)
            .click();

        // response を受け取るまで待つ
        cy.wait('@getTableList');

        // 対象のテーブルを選択
        // 左ペインのaタグのリストを取得
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, $list) => {
                // 対象のテーブル名を探す
                if ($el.text() == testTargetTableNameAlias) {
                    // 対象のテーブル名を見つけたら、要素をクリックする
                    cy.wrap($el).find('.v-list-item__title').click();
                }
            })

        // response を受け取るまで待つ
        cy.wait('@getTableData');

    });
    it('[OPU記録台帳]をクリックし画面へ遷移した後の確認', function () {

        // 1ページに表示するデータ数を選択するためのselectorが表示されていること
        cy.get(selectors.dataTableFooter).first()
            .should('be.visible');

        // data-tableに100件のデータが表示されていること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tbody').find('tr')
            .should('have.length', 100);

        // data-tableに今回アップロードしたデータが１ページ目にあること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tbody').find('tr')
            .contains('追加用カラム1')
            .should('exist');
    });
});