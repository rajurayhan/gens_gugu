const testTargetURL = '/admin/definition-upload';
const postDefinitionUploadAPI = '/api/v1/definition-bulk';

describe('画面表示', function () {
  it('画面表示', function () {
    cy.visit(testTargetURL);

    // D&Dエリアが表示されている
    cy.get('#file')
      .should('be.visible');

    // 送信ボタンが表示されている
    cy.get('#submitBtn')
      .should('be.visible');

    // プレビューエリアが表示されていない
    cy.get('#exceltable')
      .should('not.be.visible');

    // シートボタンが表示されていない
    cy.get('.sheetBtns')
      .should('be.empty');

    // データクリアボタンが表示されていない
    cy.get('#clearSheet')
      .should('not.be.visible');
  });

  it('左ペインからの画面表示', function () {
    cy.visit('/admin/tables');

    //左ペインを開く
    cy.get('i.mdi-menu').parents('button').click();
    cy.get('.v-navigation-drawer').should('be.visible');

    //左ペインからリンクをクリック
    cy.get('a[href="' + testTargetURL + '"]').children('div').click();

    // D&Dエリアが表示されている
    cy.get('#file')
      .should('be.visible');

    // 送信ボタンが表示されている
    cy.get('#submitBtn')
      .should('be.visible');

    // プレビューエリアが表示されていない
    cy.get('#exceltable')
      .should('not.be.visible');

    // シートボタンが表示されていない
    cy.get('.sheetBtns')
      .should('be.empty');

    // データクリアボタンが表示されていない
    cy.get('#clearSheet')
      .should('not.be.visible');
  });
});

describe('ロゴクリック', function () {
  it('ヘッダのロゴをクリックする', function () {
    cy.visit(testTargetURL);

    cy.get('#gens-logo').click();

    //ダッシュボードに遷移している
    cy.url().should('eq', Cypress.config().baseUrl + '/');
  });
  it('左ペインのロゴをクリックする', function () {
    cy.visit(testTargetURL);

    //open left pane
    cy.get('i.mdi-menu').parents('button').click();

    cy.get('#gens-side-logo').click();

    //ダッシュボードに遷移している
    cy.url().should('eq', Cypress.config().baseUrl + '/');
  });
});

describe('ファイル選択', function () {
  it('HTML inputでファイルを選択', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // TODO プレビューが正しく表示されていること
    // ここではプレビューが表示されていることの確認のみ。プレビューの内容が正しいかは別途目視チェックが必要
    cy.get('#exceltable')
      .should('be.visible');

    // Excelファイルの全てのシートのボタンが表示されていること
    cy.get('.singleSheetBtn')
      .should('have.length', 2);

    cy.get('.singleSheetBtn').eq(0)
      .should('have.text', 'テンプレート');
    cy.get('.singleSheetBtn').eq(1)
      .should('have.text', '記載例');

    cy.get('.singleSheetBtn')
      .each(($btn, index, $list) => {
        cy.wrap($btn).should('be.visible');
      });

    // 1つ目のシートボタンがアクティブになっていること
    cy.get('.singleSheetBtn').first()
      .should('have.class', 'active');

    // データクリアボタンが表示されていること
    cy.get('#clearSheet')
      .should('be.visible');
  });
});

describe('ファイルサイズのチェック', function () {
  it('10MB以下のファイルを選択', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // TODO 「本当に実行しますか？」というポップアップが表示されないこと

    // Excelファイルの全てのシートのボタンが表示されていること
    cy.get('.singleSheetBtn')
      .should('have.length', 2);

    cy.get('.singleSheetBtn').eq(0)
      .should('have.text', 'テンプレート');
    cy.get('.singleSheetBtn').eq(1)
      .should('have.text', '記載例');

    cy.get('.singleSheetBtn')
      .each(($btn, index, $list) => {
        cy.wrap($btn).should('be.visible');
      });

    // 1つ目のシートボタンがアクティブになっていること
    cy.get('.singleSheetBtn').first()
      .should('have.class', 'active');

    // データクリアボタンが表示されていること
    cy.get('#clearSheet')
      .should('be.visible');
  })

  it('10MB以上50MB以下のファイルを選択', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」というポップアップ（sweet alert）が表示されること
    cy.get('#swal2-title')
      .should('have.text', '本当に実行しますか？');

    // 「このファイルのサイズは xx.xx MBです。処理に時間がかかるかもしれませんが、実行しますか？」と表示されていること。
    // ※「xx.xx」にはファイルのサイズが入る
    cy.get('#swal2-content')
      .should('have.text', 'このファイルのサイズは 10.38 MBです。処理に時間がかかるかもしれませんが、実行しますか？');
  });

  it('10MB以上50MB以下のファイルを選択して、「本当に実行しますか？」でYesをクリック', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」のポップアップ（sweet alert）が表示されること
    cy.get('#swal2-title')
      .should('have.text', 'プレビューを表示しますか？');

    // 「表示まで時間がかかるかもしれません。プレビューを表示しますか？」と表示されること
    cy.get('#swal2-content')
      .should('have.text', '表示まで時間がかかるかもしれません。プレビューを表示しますか？');
  });

  it('10MB以上50MB以下のファイルを選択して、「本当に実行しますか？」でCancelをクリック', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // ポップアップが閉じること
    cy.get('#swal2-title')
      .should('be.visible');

    // プレビューが表示されないこと
    cy.get('#exceltable')
      .should('not.be.visible');
  });

  it('10MB以上50MB以下のファイルを選択して、「プレビューを表示しますか？」でYesをクリック', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // TODO プレビューが正しく表示されていること
    // ここではプレビューが表示されていることの確認のみ。プレビューの内容が正しいかは別途目視チェックが必要
    cy.get('#exceltable')
      .should('be.visible');

    // Excelファイルの全てのシートのボタンが表示されていること
    cy.get('.singleSheetBtn')
      .should('have.length', 2);

    cy.get('.singleSheetBtn').eq(0)
      .should('have.text', 'original');
    cy.get('.singleSheetBtn').eq(1)
      .should('have.text', '定義シート');

    cy.get('.singleSheetBtn')
      .each(($btn, index, $list) => {
        cy.wrap($btn).should('be.visible');
      });

    // 1つ目のシートボタンがアクティブになっていること
    cy.get('.singleSheetBtn').first()
      .should('have.class', 'active');

    // データクリアボタンが表示されていること
    cy.get('#clearSheet')
      .should('be.visible');
  });

  it('10MB以上50MB以下のファイルを選択して、「プレビューを表示しますか？」でCancelをクリック', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // プレビューが表示されないこと
    cy.get('#exceltable')
      .should('not.be.visible');

    // Excelファイルの全てのシートのボタンが表示されていること
    cy.get('.singleSheetBtn')
      .should('have.length', 2);

    cy.get('.singleSheetBtn').eq(0)
      .should('have.text', 'original');
    cy.get('.singleSheetBtn').eq(1)
      .should('have.text', '定義シート');

    cy.get('.singleSheetBtn')
      .each(($btn, index, $list) => {
        cy.wrap($btn).should('be.visible');
      });

    // 1つ目のシートボタンがアクティブになっていること
    cy.get('.singleSheetBtn').first()
      .should('have.class', 'active');

    // データクリアボタンが表示されていること
    cy.get('#clearSheet')
      .should('be.visible');
  });

  it('10MB以上50MB以下のファイルを選択して、デフォルトと別のシートをクリック', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // デフォルトで選択されているシートと別のシートをクリック
    cy.get('.singleSheetBtn').eq(1).click();

    // 「プレビューを表示しますか？」のポップアップ（sweet alert）が表示されること
    cy.get('#swal2-title')
      .should('have.text', 'プレビューを表示しますか？');

    // 「表示まで時間がかかるかもしれません。プレビューを表示しますか？」と表示されること
    cy.get('#swal2-content')
      .should('have.text', '表示まで時間がかかるかもしれません。プレビューを表示しますか？');
  });

  it('10MB以上50MB以下のファイルを選択して、2回目の「プレビューを表示しますか？」でYesをクリック', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // デフォルトで選択されているシートと別のシートをクリック
    cy.get('.singleSheetBtn').eq(1).click();

    // 2回目の「プレビューを表示しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // TODO プレビューが正しく表示されていること
    // ここではプレビューが表示されていることの確認のみ。プレビューの内容が正しいかは別途目視チェックが必要
    cy.get('#exceltable')
      .should('be.visible');

    // Excelファイルの全てのシートのボタンが表示されていること
    cy.get('.singleSheetBtn')
      .should('have.length', 2);

    cy.get('.singleSheetBtn').eq(0)
      .should('have.text', 'original');
    cy.get('.singleSheetBtn').eq(1)
      .should('have.text', '定義シート');

    cy.get('.singleSheetBtn')
      .each(($btn, index, $list) => {
        cy.wrap($btn).should('be.visible');
      });

    // 選択したシートボタンがアクティブになっていること
    cy.get('.singleSheetBtn').eq(1)
      .should('have.class', 'active');

    // データクリアボタンが表示されたままであること
    cy.get('#clearSheet')
      .should('be.visible');
  });

  it('10MB以上50MB以下のファイルを選択して、2回目の「プレビューを表示しますか？」でCancelをクリック', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // デフォルトで選択されているシートと別のシートをクリック
    cy.get('.singleSheetBtn').eq(1).click();

    // 2回目の「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // プレビューが表示されないこと
    cy.get('#exceltable')
      .should('not.be.visible');

    // Excelファイルの全てのシートのボタンが表示されていること
    cy.get('.singleSheetBtn')
      .should('have.length', 2);

    cy.get('.singleSheetBtn').eq(0)
      .should('have.text', 'original');
    cy.get('.singleSheetBtn').eq(1)
      .should('have.text', '定義シート');

    cy.get('.singleSheetBtn')
      .each(($btn, index, $list) => {
        cy.wrap($btn).should('be.visible');
      });

    // 選択したシートボタンがアクティブになっていること
    cy.get('.singleSheetBtn').eq(1)
      .should('have.class', 'active');

    // データクリアボタンが表示されたままであること
    cy.get('#clearSheet')
      .should('be.visible');
  });

  it('10MB以上50MB以下のファイルを選択して、プレビューを表示した状態で送信をクリック', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        datasource_name: 'テスト',
        table_name: 'xls_test_table'
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');

    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 送信をクリック
    cy.get('#submitBtn').click();

    // プログレスバーが表示されること
    cy.get('#progressBar')
      .should('be.visible');

    // 送信ボタンが非活性になること
    cy.get('#submitBtn')
      .should('be.disabled');

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // 定義設定成功のポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', '定義情報の設定成功');

    // 設定したデータソース名とテーブル名が表示されていること。
    cy.get('#successDialog')
      .should('include.text', 'データソース名: テスト');
    cy.get('#successDialog')
      .should('include.text', 'テーブル名: xls_test_table');
  });

  it('10MB以上50MB以下のファイルを選択して、プレビューを表示しない状態で送信をクリック', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        datasource_name: 'テスト',
        table_name: 'xls_test_table'
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');

    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // 送信をクリック
    cy.get('#submitBtn')
      .click();

    // プログレスバーが表示されること
    cy.get('#progressBar')
      .should('be.visible');

    // 送信ボタンが非活性になること
    cy.get('#submitBtn')
      .should('be.disabled');

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // 定義設定成功のポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', '定義情報の設定成功');

    // 設定したデータソース名とテーブル名が表示されていること。
    cy.get('#successDialog')
      .should('include.text', 'データソース名: テスト');
    cy.get('#successDialog')
      .should('include.text', 'テーブル名: xls_test_table');
  });

  it('10MB以上50MB以下のファイルを選択して、2回目の「プレビューを表示しますか？」でプレビューを表示した状態で送信をクリック', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        datasource_name: 'テスト',
        table_name: 'xls_test_table'
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');

    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // デフォルトで選択されているシートと別のシートをクリック
    cy.get('.singleSheetBtn').eq(1)
      .click();

    // 2回目の「プレビューを表示しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 送信をクリック
    cy.get('#submitBtn')
      .click();

    // プログレスバーが表示されること
    cy.get('#progressBar')
      .should('be.visible');

    // 送信ボタンが非活性になること
    cy.get('#submitBtn')
      .should('be.disabled');

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // 定義設定成功のポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', '定義情報の設定成功');

    // 設定したデータソース名とテーブル名が表示されていること。
    cy.get('#successDialog')
      .should('include.text', 'データソース名: テスト');
    cy.get('#successDialog')
      .should('include.text', 'テーブル名: xls_test_table');
  });

  it('10MB以上50MB以下のファイルを選択して、2回目の「プレビューを表示しますか？」でプレビューを表示しない状態で送信をクリック', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        datasource_name: 'テスト',
        table_name: 'xls_test_table'
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');

    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('10MB.xlsx');

    // 「本当に実行しますか？」でYesをクリック
    cy.get('.swal2-confirm')
      .click();

    // 「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // デフォルトで選択されているシートと別のシートをクリック
    cy.get('.singleSheetBtn').eq(1).click();

    // 2回目の「プレビューを表示しますか？」でCancelをクリック
    cy.get('.swal2-cancel')
      .click();

    // 送信をクリック
    cy.get('#submitBtn')
      .click();

    // プログレスバーが表示されること
    cy.get('#progressBar')
      .should('be.visible');

    // 送信ボタンが非活性になること
    cy.get('#submitBtn')
      .should('be.disabled');

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // 定義設定成功のポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', '定義情報の設定成功');

    // 設定したデータソース名とテーブル名が表示されていること。
    cy.get('#successDialog')
      .should('include.text', 'データソース名: テスト');
    cy.get('#successDialog')
      .should('include.text', 'テーブル名: xls_test_table');
  });

  it('50MBより大きいExcelファイルを選択', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('50MB.xlsx');

    // エラーのポップアップ（sweet alert）が表示されること
    cy.get('#swal2-title')
      .should('have.text', 'error!');

    // 「50MBより大きいファイルはアップロードできません。」と表示されること
    cy.get('#swal2-content')
      .should('have.text', '50MBより大きいファイルはアップロードできません。');

    // ポップアップのOKをクリック
    cy.get('.swal2-confirm')
      .click();

    // ポップアップが閉じること
    cy.get('#swal2-title')
      .should('be.visible');

    // プレビューが表示されないこと
    cy.get('#exceltable')
      .should('not.be.visible');
  });
});

describe('Excelファイルかどうかのチェック', function () {
  it('Excelファイル以外を選択', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('dummy.txt', { "allowEmpty": true });

    // エラーのポップアップ（sweet alert）が表示されること
    cy.get('#swal2-title')
      .should('have.text', 'error!');

    // 「おっと、それはExcelファイルではないようです。Excelファイルをアップロードしてください。」と表示されること
    cy.get('#swal2-content')
      .should('have.text', 'おっと、それはExcelファイルではないようです。Excelファイルをアップロードしてください。');

    // ポップアップのOKをクリック
    cy.get('.swal2-confirm')
      .click();

    // ポップアップが閉じること
    cy.get('#swal2-title')
      .should('be.visible');

    // プレビューが表示されないこと
    cy.get('#exceltable')
      .should('not.be.visible');
  });
});

describe('データクリアボタン', function () {
  it('データクリアボタンをクリック', function () {
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // データクリアボタンをクリック
    cy.get('#clearSheet')
      .click();

    // プレビューが消えること
    cy.get('#exceltable')
      .should('not.be.visible');

    // ドラッグ&ドロップエリアが表示されること
    cy.get('#file')
      .should('be.visible');

    // シートボタンが消えること
    cy.get('.sheetBtns').first()
      .should('be.empty');

    // データクリアボタンが消えること
    cy.get('#clearSheet')
      .should('not.be.visible');
  });
});

describe('データソースのみのアップロード', function () {
  it('すでに存在するテーブルと同じテーブル名が設定された定義ファイルのアップロード（APIスタブを使っての確認）', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        code: 30,
        message: '同じテーブルに紐づくデータソースが他にもあります。追加のデータソースとして設定しますか？\nテーブル名：table_test_new\nデータソース名：datasource_new',
        data: [],
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUploadConfirmation');

    // テスト対象の画面にアクセス
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // 送信ボタンをクリック
    cy.get('#submitBtn')
      .click();

    // プログレスバーが表示されること
    cy.get('#progressBar')
      .should('be.visible');

    // 送信ボタンが非活性になること
    cy.get('#submitBtn')
      .should('be.disabled');

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUploadConfirmation');

    // 追加確認ポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#confirmDialogTitle')
      .should('have.text', 'Attention');

    // 設定したデータソース名とテーブル名が表示されていること。
    cy.get('#confirmDialogText')
      .should('include.text', 'テーブル名')
      .should('include.text', 'データソース名');

    // プログレスバーが非表示になっていること
    cy.get('#progressBar')
      .should('not.be.visible');
  });

  it('データソースのみのアップロード - ユーザー確認後（APIスタブを使っての確認）', function () {
    // WebAPI stub の設定(正常)
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        datasource_name: 'テスト',
        table_name: 'xls_test_table',
        add_only_datasource: true,
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');


    // 前の続きから行う（APIが同じためスタブの用意不可）


    // 定義設定成功のポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');

    // ポップアップのOKをクリック
    cy.get('#confirmDialogOK').click();

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // 定義設定成功のポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', '定義情報の設定成功');

    // 設定したデータソース名とテーブル名が表示されていること。
    cy.get('#successDialog')
      .should('include.text', 'データソース名: テスト');
    cy.get('#successDialog')
      .should('include.text', 'テーブル名: xls_test_table');
  });

  it('データソースのみのアップロード - ユーザーキャンセル（APIスタブを使っての確認）', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        code: 30,
        message: '同じテーブルに紐づくデータソースが他にもあります。追加のデータソースとして設定しますか？\nテーブル名：table_test_new\nデータソース名：datasource_new',
        data: [],
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUploadConfirmation');

    // テスト対象の画面にアクセス
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // 送信ボタンをクリック
    cy.get('#submitBtn')
      .click();

    // プログレスバーが表示されること
    cy.get('#progressBar')
      .should('be.visible');

    // 送信ボタンが非活性になること
    cy.get('#submitBtn')
      .should('be.disabled');

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUploadConfirmation');

    // 定義設定成功のポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#confirmDialogTitle')
      .should('have.text', 'Attention');

    // ポップアップのOKをクリック
    cy.get('#confirmDialogCancel').click();

    // 定義設定成功のポップアップが非表示になること
    cy.get('.v-dialog')
      .should('not.be.visible');
  });
});


describe('定義ファイルアップロード', function () {
  it('定義ファイルアップロード（APIスタブを使っての確認）', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 200,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        datasource_name: 'テスト',
        table_name: 'xls_test_table'
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');

    // テスト対象の画面にアクセス
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // 送信ボタンをクリック
    cy.get('#submitBtn')
      .click();

    // プログレスバーが表示されること
    cy.get('#progressBar')
      .should('be.visible');

    // 送信ボタンが非活性になること
    cy.get('#submitBtn')
      .should('be.disabled');

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // 定義設定成功のポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', '定義情報の設定成功');

    // 設定したデータソース名とテーブル名が表示されていること。
    cy.get('#successDialog')
      .should('include.text', 'データソース名: テスト');
    cy.get('#successDialog')
      .should('include.text', 'テーブル名: xls_test_table');

    // Closeボタンをクリック
    cy.get('#dialogCloseBtn')
      .click();

    // ポップアップが消えること
    cy.get('.v-dialog')
      .should('not.be.visible');
  });

  it('パラメータエラー（APIスタブを使っての確認）', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 400,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        code: 10,
        error_message: "パラメータの値が不正です",
        error_details_count: 2,
        error_details: [
          'fileは必ず指定してください。',
          'sheet nameは必ず指定してください。'
        ]
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');

    // テスト対象の画面にアクセス
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // 送信ボタンをクリック
    cy.get('#submitBtn')
      .click();

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // パラメータエラーのポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', 'パラメータの値が不正です');

    // 設定したデータソース名とテーブル名が表示されていること。
    cy.get('#parameterErrorDialog')
      .should('include.text', 'エラー件数: 2');
    cy.get('#parameterErrorDialog')
      .should('include.text', 'fileは必ず指定してください。');
    cy.get('#parameterErrorDialog')
      .should('include.text', 'sheet nameは必ず指定してください。');

    // Closeボタンをクリック
    cy.get('#dialogCloseBtn')
      .click();

    // ポップアップが消えること
    cy.get('.v-dialog')
      .should('not.be.visible');
  });
});

describe('その他のエラー', function () {
  it('CSRFトークンエラー（APIスタブを使っての確認）', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 419,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');

    // テスト対象の画面にアクセス
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // 送信ボタンをクリック
    cy.get('#submitBtn')
      .click();

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // システムエラーのポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', 'セッションが切れました。リロードしてください。');

    // Closeボタンをクリック
    cy.get('#dialogCloseBtn')
      .click();

    // ポップアップが消えること
    cy.get('.v-dialog')
      .should('not.be.visible');
  });

  it('システムエラー（APIスタブを使っての確認）', function () {
    // WebAPI stub の設定
    cy.server();
    cy.route({
      method: 'POST',
      url: postDefinitionUploadAPI,
      status: 500,
      headers: {
        'content-type': 'application/json; charset=utf-8'
      },
      response: {
        code: 10,
        error_message: "予期せぬエラーが発生しました。",
        error_details_count: 0,
        error_details: []
      },
      delay: 1000 // レスポンス待ちの画面をassertするために設定する
    })
      .as('postDefinitionUpload');

    // テスト対象の画面にアクセス
    cy.visit(testTargetURL);

    // ファイルを選択
    cy.get('#file').attachFile('DefinitionUpload.xlsx');

    // 送信ボタンをクリック
    cy.get('#submitBtn')
      .click();

    // response を受け取るまで待つ
    cy.wait('@postDefinitionUpload');

    // システムエラーのポップアップが表示されること
    cy.get('.v-dialog')
      .should('be.visible');
    cy.get('#vCardTitle')
      .should('have.text', '予期せぬエラーが発生しました。');

    // Closeボタンをクリック
    cy.get('#dialogCloseBtn')
      .click();

    // ポップアップが消えること
    cy.get('.v-dialog')
      .should('not.be.visible');
  });
});
