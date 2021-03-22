const testTargetURL = '/fileupload';
const postFileUploadAPI = '/upload-excel';
const postGetDataSourceListAPI = '/api/v1/datasources';
const files = {
  fileUploadExcel: 'fileUploadExcel.xlsx',
  fileUploadCsv: 'fileUploadCsv.csv',
  fileUploadCsvSjis: 'fileUploadCsv_SJIS.csv',
};
const selectors = {
  // ロゴ
  logo: '#gens-logo',
  // 左ペイン内のロゴ
  leftPaneLogo: '#gens-side-logo',
  // 左ペイン
  drawerButton: '.v-app-bar__nav-icon',
  // タイトル
  title: '.v-card__title .display-1',
  // 「アップロードするファイル」プルダウン
  datasourceSelect: '#datasourceSelect',
  // 「アップロードするファイル」プルダウン バリエーションメッセージ
  datasourceSelectValidation: '.col-5 .v-messages__message',
  // 対象データソース
  targetDatasource: 'データソース1',
  // 「開始行」テキストボックス
  startrow: '#startrow',
  // 「開始行」テキストボックス バリデーション
  startrowValidation: '.col-2 .v-messages__message',
  // アップロード形式「追加」ラジオボタン
  modeAppend: '#modeAppend',
  // D&Dエリア
  dAndDArea: '#file',
  // プレビューエリア
  previewArea: '#exceltable',
  // シートボタン
  sheetBtn: '.sheetBtns',
  // データクリアボタン
  dataClearBtn: '#clearSheetBtn',
  // 送信ボタン
  submitBtn: '#submitBtn',
  // トップのプログレスバー
  progressBarTop: '#progressBarTop',
  // ボトムのプログレスバー
  progressBarBottom: '#progressBarBottom',
  // Excelファイルの全てのシートのボタン
  singleSheetBtn: '.singleSheetBtn',
  // アップロード成功ポップアップ
  successPopUp: '.v-dialog',
  // アップロード成功ポップアップ タイトル
  successPopUpTitle: '#vResCardTitle',
  // アップロード成功ポップアップ 詳細
  successPopUpDetail: '#successDialogText',
  // アップロード成功ポップアップ 閉じるボタン
  successPopUpCloseBtn: '#closeBtn',
  // エラーモーダル タイトル
  errorModalTitle: '.v-dialog.v-dialog--active.v-dialog--persistent.v-dialog--scrollable .v-card__title',
  // エラーモーダル 詳細
  errorModalDetail: '#elseErrorDialogText',
  // エラーポップアップ タイトル
  errorPopUpTitle: '#swal2-title',
  // エラーポップアップ 詳細
  errorPopUpDetail: '#swal2-content',
  // エラーポップアップ OKボタン
  errorPopUpOkBtn: '.swal2-confirm',
};
const apiResponseArray = [
  {
    "id": 1,
    "datasource_name": "データソース1",
    "table_id": 1,
    "starting_row_number": null,
    "table_name": "table1"
  },
  {
    "id": 2,
    "datasource_name": "データソース2",
    "table_id": 2,
    "starting_row_number": null,
    "table_name": "table2"
  },
  {
    "id": 3,
    "datasource_name": "データソース3",
    "table_id": 2,
    "starting_row_number": null,
    "table_name": "table3"
  },
  {
    "id": 4,
    "datasource_name": "データソース4",
    "table_id": 2,
    "starting_row_number": null,
    "table_name": "table4"
  },
  {
    "id": 5,
    "datasource_name": "データソース5",
    "table_id": 3,
    "starting_row_number": null,
    "table_name": "table5"
  },
];

describe('初期表示', function () {
  it('ファイルアップロード画面を開く', function () {
    cy.visit(testTargetURL);

    // タイトルに「ファイルアップロード」と表示されている
    cy.get(selectors.title)
      .should('be.visible')
      .contains('ファイルアップロード');

    // 「アップロードするファイル」のドロップダウンリストが表示されている
    cy.get(selectors.datasourceSelect)
      .should('be.visible')

    // 「データ開始行」のテキストボックスが表示されている
    cy.get(selectors.startrow)
      .should('be.visible')

    // アップロード形式「追加」のラジオボタンが表示され選択されている
    cy.get(selectors.modeAppend)
      .should('be.visible')
      .should('be.checked')

    // D&Dエリアが表示されている
    cy.get(selectors.dAndDArea)
      .should('be.visible');

    // 送信ボタンが表示されている
    cy.get(selectors.submitBtn)
      .should('be.visible');

    // プレビューエリアが表示されていない
    cy.get(selectors.previewArea)
      .should('not.be.visible');

    // シートボタンが表示されていない
    cy.get(selectors.sheetBtn)
      .should('be.empty');

    // データクリアボタンが表示されていない
    cy.get(selectors.dataClearBtn)
      .should('not.be.visible');
  });
});

describe('ロゴクリック', function () {
  it('ヘッダのロゴをクリックする', function () {
    cy.visit(testTargetURL);

    cy.get(selectors.logo).click();

    //ダッシュボードに遷移している
    cy.url().should('eq', Cypress.config().baseUrl + '/');
  });
  it('左ペインのロゴをクリックする', function () {
    cy.visit(testTargetURL);

    //open left pane
    cy.get(selectors.drawerButton).click();

    cy.get(selectors.leftPaneLogo).click();

    //ダッシュボードに遷移している
    cy.url().should('eq', Cypress.config().baseUrl + '/');
  });
});

describe('項目表示', function () {
  it('ドロップダウンリスト「アップロードするファイル」をクリック', function () {
    cy.server();

    // datasource取得APIのスタブ
    cy.route({
      method: 'GET',
      url: postGetDataSourceListAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        count: apiResponseArray.length,
        datasources: apiResponseArray
      },
    })
      .as('postGetDataSourceList');

    cy.visit(testTargetURL);

    // 「アップロードするファイル」をクリック。データソース一覧を表示するため
    cy.get(selectors.datasourceSelect)
      .click({ force: true });

    // response を受け取るまで待つ
    cy.wait('@postGetDataSourceList');

    // データソース一覧が正しく表示されること
    cy.get('.menuable__content__active >> .v-list-item').each(($el, index, $list) => {
      cy.wrap($el).should('have.text', apiResponseArray[index]['datasource_name']);
    });
  });

  it('ドロップダウンリスト「アップロードするファイル」をクリック。そのドロップダウンリストに何も選択していない状態で「送信」ボタンをクリック', function () {
    cy.server();

    // datasource取得APIのスタブ
    cy.route({
      method: 'GET',
      url: postGetDataSourceListAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        count: 0,
        datasources: []
      },
    })
      .as('postGetDataSourceList');

    cy.visit(testTargetURL);

    // 「アップロードするファイル」をクリック。データソース一覧を表示するため
    cy.get(selectors.datasourceSelect)
      .click({ force: true });

    // データソース一覧が表示されないこと
    cy.get('.menuable__content__active')
      .should('have.text', 'No data available');

    // 送信ボタンをクリック
    cy.get(selectors.submitBtn)
      .click();

    // バリデーションエラーが表示されること
    cy.get(selectors.datasourceSelectValidation)
      .should('be.visible')
      .should('have.text', 'アップロードするファイルを指定してください');

    // TODO ファイルアップロードされないことを確認する。
  });

  it('ファイルアップロード画面を開く。500エラーの場合、エラーメッセージが表示される', function () {
    cy.server();

    // datasource取得APIのスタブ
    cy.route({
      method: 'GET',
      url: postGetDataSourceListAPI,
      status: 500,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
      },
    })
      .as('postGetDataSourceList');

    cy.visit(testTargetURL);

    cy.get(selectors.errorModalTitle)
      .should('have.text', 'システムエラーが発生しました。IT部門に連絡してください');

    cy.get(selectors.errorModalDetail)
      .should('have.text', '\n          詳細：\n          \n          アップロード画面の準備に失敗しました。\n        ');
  });
});

describe('入力チェック', function () {
  it.skip('ドロップダウンリスト「アップロードするファイル」をクリックし、何も選択せずに他のフィールドをクリック', function () {
    cy.visit(testTargetURL);

    cy.get(selectors.datasourceSelect)
      .click({ force: true });

    // TODO データソース一覧からフォーカスを外す

    cy.get(selectors.datasourceSelectValidation)
      .should('be.visible')
      .should('have.text', 'アップロードするファイルを指定してください');
  });

  it('「データ開始行」のテキストボックスをクリックし、何も入力せずに他のフィールドをクリック', function () {
    cy.visit(testTargetURL);

    cy.get(selectors.startrow)
      .focus()
      .blur();

    cy.get(selectors.startrowValidation)
      .should('be.visible')
      .should('have.text', 'データ開始行を指定してください');
  });

  it('以下を指定し、送信ボタンをクリック・アップロードするファイル：何も選択しない・データ開始行：何も入力しない・ファイル：何も選択しない', function () {
    cy.visit(testTargetURL);

    cy.get(selectors.submitBtn)
      .click();

    cy.get(selectors.datasourceSelectValidation)
      .should('be.visible')
      .should('have.text', 'アップロードするファイルを指定してください');

    cy.get(selectors.startrowValidation)
      .should('be.visible')
      .should('have.text', 'データ開始行を指定してください');
  });

  it('以下を指定し、送信ボタンをクリック・アップロードするファイル：何も選択しない・データ開始行：1～1048576の任意の数値・ファイル：任意のExcelファイル', function () {
    cy.visit(testTargetURL);

    cy.get(selectors.startrow)
      .type(2);

    cy.get(selectors.dAndDArea).attachFile(files.fileUploadExcel);

    cy.get(selectors.submitBtn)
      .click();

    cy.get(selectors.datasourceSelectValidation)
      .should('be.visible')
      .should('have.text', 'アップロードするファイルを指定してください');
  });

  it('以下を指定し、送信ボタンをクリック・アップロードするファイル：任意の項目・データ開始行：何も入力しない・ファイル：任意のExcelファイル', function () {
    cy.server();

    // datasource取得APIのスタブ
    cy.route({
      method: 'GET',
      url: postGetDataSourceListAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        count: apiResponseArray.length,
        datasources: apiResponseArray
      }
    })
      .as('postGetDataSourceList');

    cy.visit(testTargetURL);

    cy.get(selectors.datasourceSelect)
      .click({ force: true });

    // response を受け取るまで待つ
    cy.wait('@postGetDataSourceList');

    cy.contains(selectors.targetDatasource)
      .click();

    cy.get(selectors.dAndDArea).attachFile(files.fileUploadExcel);

    cy.get(selectors.submitBtn)
      .click();

    cy.get(selectors.startrowValidation)
      .should('be.visible')
      .should('have.text', 'データ開始行を指定してください');
  });

  it('「データ開始行」のテキストボックスに「0」を入力し、他のフィールドをクリック', function () {
    cy.visit(testTargetURL);

    cy.get(selectors.startrow)
      .type(0);

    cy.get(selectors.startrowValidation)
      .should('be.visible')
      .should('have.text', 'データ開始行は1以上を指定してください');
  });

  it('以下を指定し、送信ボタンをクリック・アップロードするファイル：任意の項目・データ開始行：0・ファイル：任意のExcelファイル', function () {
    cy.server();

    // datasource取得APIのスタブ
    cy.route({
      method: 'GET',
      url: postGetDataSourceListAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        count: apiResponseArray.length,
        datasources: apiResponseArray
      }
    })
      .as('postGetDataSourceList');

    cy.visit(testTargetURL);

    cy.get(selectors.datasourceSelect)
      .click({ force: true });

    // response を受け取るまで待つ
    cy.wait('@postGetDataSourceList');

    cy.contains(selectors.targetDatasource)
      .click();

    cy.get(selectors.startrow)
      .type(0);

    cy.get(selectors.dAndDArea).attachFile(files.fileUploadExcel);

    cy.get(selectors.submitBtn)
      .click();

    cy.get(selectors.startrowValidation)
      .should('be.visible')
      .should('have.text', 'データ開始行は1以上を指定してください');
  });

  it('「データ開始行」のテキストボックスに「1048577」を入力し、他のフィールドをクリック', function () {
    cy.visit(testTargetURL);

    cy.get(selectors.startrow)
      .type(1048577);

    cy.get(selectors.startrowValidation)
      .should('be.visible')
      .should('have.text', 'データ開始行は1048576以下を指定してください');
  });

  it('以下を指定し、送信ボタンをクリック・アップロードするファイル：任意の項目・データ開始行：1048577・ファイル：任意のファイル', function () {
    cy.server();

    // datasource取得APIのスタブ
    cy.route({
      method: 'GET',
      url: postGetDataSourceListAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        count: apiResponseArray.length,
        datasources: apiResponseArray
      }
    })
      .as('postGetDataSourceList');

    cy.visit(testTargetURL);

    cy.get(selectors.datasourceSelect)
      .click({ force: true });

    // response を受け取るまで待つ
    cy.wait('@postGetDataSourceList');

    cy.contains(selectors.targetDatasource)
      .click();

    cy.get(selectors.startrow)
      .type(1048577);

    cy.get(selectors.dAndDArea).attachFile(files.fileUploadExcel);

    cy.get(selectors.submitBtn)
      .click();

    cy.get(selectors.startrowValidation)
      .should('be.visible')
      .should('have.text', 'データ開始行は1048576以下を指定してください');
  });
});

// TODO csvファイルアップロード前からある項目のうちNo.14～21、31～46、49、50、63～66は未実装

describe('ファイル選択', function () {
  it('ファイル選択エリアをクリックし、任意のcsvファイルを選択', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get(selectors.dAndDArea).attachFile(files.fileUploadCsv);

    // ここではプレビューが表示されていることの確認のみ。プレビューの内容が正しいかは別途目視チェックが必要
    cy.get(selectors.previewArea)
      .should('be.visible');

    // Excelファイルの全てのシートのボタンが表示されていないこと
    cy.get(selectors.singleSheetBtn)
      .should('not.be.visible');

    // データクリアボタンが表示されていること
    cy.get(selectors.dataClearBtn)
      .should('be.visible');
  });
});

describe('Excelファイルまたはcsvファイルかどうかのチェック', function () {
  it('Excelファイルでもcsvファイルでもないファイルを選択', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get(selectors.dAndDArea).attachFile('dummy.txt', { "allowEmpty": true });

    // エラーのポップアップ（sweet alert）が表示されること
    cy.get(selectors.errorPopUpTitle)
      .should('have.text', 'error!');

    // 「おっと、そのファイルはアップロードできません。Excelファイルかcsvファイルを選択してください。」と表示されること
    cy.get(selectors.errorPopUpDetail)
      .should('have.text', 'おっと、そのファイルはアップロードできません。Excelファイルかcsvファイルを選択してください。');
  });

  it('エラーのポップアップの「OK」ボタンをクリック', function () {
    // ポップアップのOKをクリック
    cy.get(selectors.errorPopUpOkBtn)
      .click();

    // ポップアップが閉じること
    cy.get(selectors.errorPopUpTitle)
      .should('be.visible');

    // プレビューが表示されないこと
    cy.get(selectors.previewArea)
      .should('not.be.visible');
  });
});

describe('csvファイルのアップロード', function () {
  it('csvファイル（文字コード SJIS）', function () {
    cy.server();

    // datasource取得APIのスタブ
    cy.route({
      method: 'GET',
      url: postGetDataSourceListAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        count: apiResponseArray.length,
        datasources: apiResponseArray
      }
    })
      .as('postGetDataSourceList');

    // ファイルアップロードAPIのスタブ
    cy.route({
      method: 'POST',
      url: postFileUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        meta: {
          code: 10,
          file_name: files.fileUploadCsvSjis,
          sheet_name: '-',
          mode: '追加'
        }
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postFileUploadCsv_SJIS');

    // テスト対象の画面にアクセス
    cy.visit(testTargetURL);

    // 「アップロードするファイル」のドロップダウンリストから選択
    cy.get(selectors.datasourceSelect)
      .click({ force: true });

    // response を受け取るまで待つ
    cy.wait('@postGetDataSourceList');

    // 「アップロードするファイル」のドロップダウンリストの一番上の項目を選択
    cy.contains(selectors.targetDatasource)
      .click();

    // 開始行に「2」を入力
    cy.get(selectors.startrow)
      .type(2);

    // ファイルを選択
    cy.get(selectors.dAndDArea).attachFile(files.fileUploadCsvSjis);

    // 送信ボタンをクリック
    cy.get(selectors.submitBtn)
      .click();

    // 送信ボタンが非活性になること
    cy.get(selectors.submitBtn)
      .should('be.disabled');

    // プログレスバーが表示されること
    cy.get(selectors.progressBarTop)
      .should('be.visible');

    // プログレスバーが表示されること
    cy.get(selectors.progressBarBottom)
      .should('be.visible');

    // response を受け取るまで待つ
    cy.wait('@postFileUploadCsv_SJIS');

    // アップロード成功のポップアップが表示されること
    cy.get(selectors.successPopUp)
      .should('be.visible');
    cy.get(selectors.successPopUpTitle)
      .should('have.text', 'アップロード成功');

    // 設定したファイル名とシート名が表示されていること。
    cy.get(selectors.successPopUpDetail)
      .should('include.text', `ファイル名: ${files.fileUploadCsvSjis}`)
      .should('include.text', 'シート名: -')
      .should('include.text', 'アップロード形式: 追加');

    // Closeボタンをクリック
    cy.get(selectors.successPopUpCloseBtn)
      .click();

    // ポップアップが消えること
    cy.get(selectors.successPopUp)
      .should('not.be.visible');
  });

  it('csvファイル（文字コード UTF-8）（項目No.53～57も同時確認）', function () {
    cy.server();

    // datasource取得APIのスタブ
    cy.route({
      method: 'GET',
      url: postGetDataSourceListAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        count: apiResponseArray.length,
        datasources: apiResponseArray
      }
    })
      .as('postGetDataSourceList');

    // ファイルアップロードAPIのスタブ
    cy.route({
      method: 'POST',
      url: postFileUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        meta: {
          code: 10,
          file_name: files.fileUploadCsv,
          sheet_name: '-',
          mode: '追加'
        }
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postFileUploadCsv');

    // テスト対象の画面にアクセス
    cy.visit(testTargetURL);

    // 「アップロードするファイル」のドロップダウンリストから選択
    cy.get(selectors.datasourceSelect)
      .click({ force: true });

    // response を受け取るまで待つ
    cy.wait('@postGetDataSourceList');

    // 「アップロードするファイル」のドロップダウンリストの一番上の項目を選択
    cy.contains(selectors.targetDatasource)
      .click();

    // 開始行に「2」を入力
    cy.get(selectors.startrow)
      .type(2);

    // ファイルを選択
    cy.get('#file').attachFile(files.fileUploadCsv);

    // 送信ボタンをクリック
    cy.get(selectors.submitBtn)
      .click();

    // 送信ボタンが非活性になること
    cy.get(selectors.submitBtn)
      .should('be.disabled');

    // プログレスバーが表示されること
    cy.get(selectors.progressBarTop)
      .should('be.visible');

    // プログレスバーが表示されること
    cy.get(selectors.progressBarBottom)
      .should('be.visible');

    // response を受け取るまで待つ
    cy.wait('@postFileUploadCsv');

    // アップロード成功のポップアップが表示されること
    cy.get(selectors.successPopUp)
      .should('be.visible');
    cy.get(selectors.successPopUpTitle)
      .should('have.text', 'アップロード成功');

    // 設定したファイル名とシート名が表示されていること。
    cy.get(selectors.successPopUpDetail)
      .should('include.text', `ファイル名: ${files.fileUploadCsv}`)
      .should('include.text', 'シート名: -')
      .should('include.text', 'アップロード形式: 追加');

    // Closeボタンをクリック
    cy.get(selectors.successPopUpCloseBtn)
      .click();

    // ポップアップが消えること
    cy.get(selectors.successPopUp)
      .should('not.be.visible');
  });
});
