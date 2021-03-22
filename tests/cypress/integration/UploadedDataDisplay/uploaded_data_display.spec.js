const testTargetURL = '/tabledata';
const getTableListAPI = '/api/v1/tables';
// vuetify(?)により左ペイン表示の際に半角スペースが挿入されるので付けておく
const testTargetTableNameAlias = ' テスト用_全カラムタイプ(E2Eテスト用)';
const testTableWithData = ' テスト用_全カラムタイプ(E2Eテスト用)';
const testTableWithoutData = ' データのないテストテーブル（E2Eテスト）';
const testTableWithoutDataAndColumn = ' 列とデータのないテストテーブル（E2Eテスト）';

const selectors = {
    // ロゴ
    logo: '#gens-logo',
    // 左ペイン内のロゴ
    leftPaneLogo: '#gens-side-logo',
    // 左ペインを開くためのボタン
    drawerButton: '#drawerButton',
    // 左ペイン
    navigationDrawer: '#navidationDrawer',
    // データ表示エリア（data-table）
    dataTableUploadedData: '#dataTableUploadedData',
    // データ表示エリアのタイトル
    dataTableTitle: '#dataTableTitle',
    // サーチボックス
    searchBox: '#searchBox',
    // データ表示エリアのフッター
    dataTableFooter: '.v-data-footer',
    // 1ページに表示しているデータ数（ここをクリックするとデータ数の選択肢が表示される）
    recordsNumPerPage: '.v-select__slot',
    // 1ページに表示しているデータ数の選択肢
    recordsNumPerPageOptions: '.v-select-list',
    // snack bar
    snackBar: 'snackBar',
    //Download button
    downloadBtn: '.mdi-arrow-down-bold-box-outline',
}


describe('ロゴクリック', function () {
    it('ヘッダのロゴをクリックする', function () {
        cy.visit(testTargetURL);

        cy.get('.v-overlay.v-overlay--active').click();

        cy.get(selectors.logo).click({
            force: true
        });

        //ダッシュボードに遷移している
        cy.url().should('eq', Cypress.config().baseUrl + '/');
    });
    it('左ペインのロゴをクリックする', function () {
        cy.visit(testTargetURL);

        cy.get(selectors.leftPaneLogo).click();

        //ダッシュボードに遷移している
        cy.url().should('eq', Cypress.config().baseUrl + '/');
    });
});

describe('1ページに表示するデータ数', function () {
    it('100件以上のデータを表示（1ページのデータ数はデフォルト値）', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        cy.visit(testTargetURL);

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

        // 1ページに表示するデータ数を選択するためのselecterが表示されていること
        cy.get(selectors.dataTableFooter).first()
            .should('be.visible');

        // data-tableに100件のデータが表示されていること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tr')
            .should('have.length', 101); // ヘッダー行含めて101行
    });

    it('1000件以上のデータを表示し、1ページに表示するデータ数を1000件に変更する', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        cy.visit(testTargetURL);

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

        // 1ページに表示するデータ数を選択するためのselecterで1000件を選択する
        cy.get(selectors.recordsNumPerPage).click();
        cy.get(selectors.recordsNumPerPageOptions)
            .each(($el, index, $list) => {
                cy.wrap($el).contains('1000').click();
            })

        // data-tableに1000件のデータが表示されていること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tr')
            .should('have.length', 1001); // ヘッダー行含めて1001行
    });

    it('100件以上のデータを表示し、1ページに表示するデータ数を100件に変更する', function () {
        cy.server();

        // テーブル一覧取得API（waitのために設定しておく）
        cy.route("get", getTableListAPI).as('getTableList');

        cy.visit(testTargetURL);

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

        // 1ページに表示するデータ数を選択するためのselecterで1000件を選択する
        cy.get(selectors.recordsNumPerPage).click();
        cy.get(selectors.recordsNumPerPageOptions)
            .each(($el, index, $list) => {
                cy.wrap($el).contains('1000').click();
            })

        // 1ページに表示するデータ数を選択するためのselecterで100件を選択する
        cy.get(selectors.recordsNumPerPage).click();
        cy.get(selectors.recordsNumPerPageOptions)
            .each(($el, index, $list) => {
                cy.wrap($el).contains('100').click();
            })

        // data-tableに100件のデータが表示されていること
        cy.get(selectors.dataTableUploadedData)
            .find('table').find('tr')
            .should('have.length', 101); // ヘッダー行含めて101行
    });
});

// Merging Starts
describe('Check Existance of Table, Columns and Data', function () {
    it('Click on the "Table name alias"from left side-bar: Table has no column and data', function () {
        cy.visit(testTargetURL);

        // wait to load data
        cy.wait(3000);

        // select the target table
        // Get the list of a tags in the left pane
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, list) => {
                var textContent = $el.text();
                if (textContent == testTableWithoutDataAndColumn) {
                    cy.wrap($el).find('.v-list-item__title').click();
                    //Check title
                    cy.get('.v-toolbar__title').contains('列とデータのないテストテーブル（E2Eテスト）');
                    // Check for Table Header (Column) and length
                    cy.get(selectors.dataTableUploadedData).find('table').find('thead')
                        .contains('アップロード日時').should('length.not.be.gt', 1);
                    // Check for Empty Table Boddy
                    cy.get(selectors.dataTableUploadedData)
                        .find('table').find('.v-data-table__empty-wrapper')
                        .should('be.visible');
                }
            })
    });

    it('Click on the "Table name alias"from left side-bar: Table has column but no data', function () {
        // display table list
        cy.get(selectors.drawerButton).click();

        // select the target table
        // Get the list of a tags in the left pane
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, list) => {
                var textContent = $el.text();
                if (textContent == testTableWithoutData) {
                    cy.wrap($el).find('.v-list-item__title').click();

                    //Check title
                    cy.get('.v-toolbar__title').contains('データのないテストテーブル（E2Eテスト）');

                    //Check for Table Header (Column) and length
                    cy.get(selectors.dataTableUploadedData)
                        .find('table').find('thead').find('tr').find('th').should('have.length', 11);
                    // Check for Empty Table Boddy
                    cy.get(selectors.dataTableUploadedData)
                        .find('table').find('.v-data-table__empty-wrapper')
                        .should('be.visible');
                }
            })
    });

    it('Click on the "Table name alias"from left side-bar: Table has column and data', function () {
        // display table list
        cy.get(selectors.drawerButton).click();

        // select the target table
        // Get the list of a tags in the left pane
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, list) => {
                var textContent = $el.text();
                if (textContent == testTableWithData) {
                    cy.wrap($el).find('.v-list-item__title').click();

                    // Check for Table Header(Column) length
                    cy.get(selectors.dataTableUploadedData)
                        .find('table').find('thead').find('tr').find('th').should('have.length', 11);

                    // Check for Table Body and row number(100 for first page)
                    cy.get(selectors.dataTableUploadedData)
                        .find('table').find('tbody').find('tr')
                        .should('have.length', 100);
                }
            })
    });
});

describe('Download File', function () {
    it('Click on the "Download Icon " right corner: It will download existing data as an excel file.', function () {
        cy.visit(testTargetURL);

        cy.server();
        cy.route("GET", "/api/v1/table-data/1?sortBy[]=created_at&sortDesc[]=true").as("getTableDetails");

        //wait to load data
        cy.wait(3000);

        // select the target table
        // Get the list of a tags in the left pane
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, list) => {
                var textContent = $el.text();
                if (textContent == testTableWithData) {
                    cy.wrap($el).find('.v-list-item__title').click();
                    // Wait to load data.
                    cy.wait('@getTableDetails');
                    // Click on download button
                    cy.get(selectors.downloadBtn).click();
                }
            })
    });

    it('Click on the "Download Icon " right corner: It will show an error message There is no data!', function () {

        // display table list
        cy.get(selectors.drawerButton).click();

        // select the target table
        // Get the list of a tags in the left pane
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, list) => {
                var textContent = $el.text();
                if (textContent == testTableWithoutData) {
                    cy.wrap($el).find('.v-list-item__title').click();
                    // check download button disable
                    cy.get('button').eq(2).should('be.disabled');
                }
            })
    });

    it('Click on the "Download Icon " right corner: It will show an error message There is no data!', function () {

        // display table list
        cy.get(selectors.drawerButton).click();

        // select the target table
        // Get the list of a tags in the left pane
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, list) => {
                var textContent = $el.text();
                if (textContent == testTableWithoutDataAndColumn) {
                    cy.wrap($el).find('.v-list-item__title').click();

                    // check download button disable
                    cy.get('button').eq(2).should('be.disabled');
                }
            })
    });
});

describe('Check uploaded data types', function () {
    it('Check bigint type data', function () {
        cy.visit(testTargetURL);
        //wait to load data
        cy.wait(3000);

        cy.server();
        cy.route("GET", "/api/v1/table-data/1?page=1&itemsPerPage=100&sortBy[]=created_at&sortDesc[]=true").as("getTabledata");

        // select the target table
        // Get the list of a tags in the left pane
        cy.get(selectors.navigationDrawer).find('a')
            .each(($el, index, list) => {
                var textContent = $el.text();
                if (textContent == testTableWithData) {
                    cy.wrap($el).find('.v-list-item__title').click();
                    cy.wait('@getTabledata');
                    // Check for Table data
                    cy.get('table').within(($table) => {
                        //check data in specific row and column
                        cy.get('tbody').find('tr').eq(0).should('contain', '1');
                        cy.get('tr').find('td').eq(1).should('contain', '1');
                    })
                }
            })
    });

    it('check varchar type data', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').eq(0).should('contain', '文字列1');
            cy.get('tbody').find('tr').eq(0).find('td').eq(5).should('contain', '文字列1');
        })
    });

    it('check string with Japanese text', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            cy.get('tbody').find('tr').should('contain', '文字列');
        })
    });

    it('check string with machine-dependent characters', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            cy.get('tbody').find('tr').eq(0).should('contain', '♂♀');
        })
    });

    it('check string with half-width kana', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            cy.get('tbody').find('tr').should('contain', 'ｱｲｳｴｵ');
        })
    });

    it('check string with commas', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').should('contain', ',');
        })
    });

    it('Check string with double qoutation', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').should('contain', '"');
        })
    });

    it('check Decimal number', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').should('contain', '0.1');
        })
    });

    it('check Date-time', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').should('contain', '2020-07-29 14:21:24');
        })
    });


    it('Check empty for bigint type data', function () {
        cy.server();

        //Get 1000 items per page
        cy.route("GET", "/api/v1/table-data/1?page=1&itemsPerPage=1000&sortBy[]=created_at&sortDesc[]=true").as("get1000PerPage");
        //Load second page
        cy.route("GET", "/api/v1/table-data/1?page=2&itemsPerPage=1000&sortBy[]=created_at&sortDesc[]=true").as("getSecondPage");

        // Click for pages
        cy.get('.v-data-footer__select').within(($footer) => {
            cy.get('.v-input__append-inner').click();
        })

        //get 1000 items per page
        cy.get('.v-list-item__content').within(($perPage) => {
            cy.get('.v-list-item__title').each(($el, index, $list) => {
                var txt = $el.text();
                if (txt == 1000) {
                    $el.click();
                    cy.wait('@get1000PerPage');
                }
            });
        })
        //Go to the next page
        cy.get('.v-data-footer__icons-after').click();
        cy.wait('@getSecondPage');

        // Check Table data in specific row
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').should('contain', '');
        })
    });

    it('check minimum Decimal number', function () {


        // Check for Table data
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').should('contain', '0.0');
            cy.get('tbody').find('tr').find('td').should('contain', '0.0');

            cy.get('tr').contains('td', '0.0').should('have.length.greaterThan', 0).should('be', 'decimal');
        })
    });


    it('check empty for Decimal type data', function () {


        // Check for Table data
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').should('contain', '');
        })
    });


    it('check empty for Date-time', function () {


        // Check for Table data
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').eq(-1).should('contain', '');
            cy.get('tbody').find('tr').eq(-1).find('td').eq(3).should('contain', '');
        })
    });

    it('Check maximum varchar type data', function () {

        // Check for Table data
        cy.get('table').within(($table) => {
            cy.get('tbody').find('tr').should('contain', 'Maximum charecter length examples... klnsduhfusdhjflhsdiuytejrtiodfhuihgiosdhiughsuidhfhfdgnjsfdghduirgg klnsduhfusdhjflhsdiuytejrtiodfhuihgiosdhiughsuidhfhfdgnjsfdghduirgg klnsduhfusdhjflhsdiuytejrtiodfhuihgiosdhiughsuidhfhfdgnjsfdghduirklnsduhfusdhjflhs');
        })
    });

    it('Check minimum varchar type data', function () {
        // Check for Table data
        cy.get('table').within(($table) => {
            cy.get('tbody').find('tr').should('contain', 'a');
        })
    });

    it('Check null for varchar value, it should be empty', function () {
        // Check Table data in specific row
        cy.get('table').within(($table) => {
            cy.get('tbody').find('tr').should('contain', '');
        })
    });

    it('Check empty for varchar value', function () {


        // Check Table data in specific row
        cy.get('table').within(($table) => {
            //check data in specific row and column
            cy.get('tbody').find('tr').should('contain', '');
        })
    });
});
