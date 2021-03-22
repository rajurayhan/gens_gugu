const dashboardURL = '/';
const adminURL = '/admin';
const tableListURL = '/admin/tables';
// const tableColumnListsURL = '/admin/list';
// const datasourceURL = '/admin/datasource';
const datasourceColumnsURL = '/admin/datasource-columns';

const postDefinitionUploadAPI = '/api/v1/definition-bulk';
const postFileUploadAPI = '/upload-excel';

const getFileListAPI = '/api/v1/excel_files';
const getTableAPI = '/api/v1/tables';
const getTableColumnsAPI = '/api/v1/table-columns';
const getDatasourceAPI = '/api/get/data-source/all';
const getDatasourceColumnAPI = '/api/get/datasource-columns';
const postTableAddAPI = '/api/v1/add/tables';
const postTableColumnAddAPI = '/api/v1/add/table-columns';
const postDatasourceAddAPI = '/api/add/data-source';
const postDatasourceColumnAddAPI = '/api/add/datasource-columns';
const postTableDeleteAPI = '/api/v1/delete/tables';
const postTableColumnDeleteAPI = '/api//v1/delete/table-columns';
const postDatasourceDeleteAPI = '/api/delete/data-source';
const postDatasourceColumnDeleteAPI = '/api/delete/datasource-columns';
const getDataSourceListAPI = '/api/v1/datasources';
const files = {
    path: 'OPU/',
    definitionUploadExcel: 'GENS定義情報設定シート_採卵・OPU.xlsx',
};
const formTitle = {
    new: "New Item",
    edit: "Edit Item"
};
const tableFieldName = {
    table_name: 'Table Name',
    table_name_alias: 'Table Name Alias',
};
const tableFieldData = {
    table_name: 'test_table_name',
    table_name_alias: 'test_table_alias',
};
const tableColumnFieldName = {
    table_name: 'Table Name',
    column_name: 'Column Name',
    column_name_alias: 'Column Name Alias',
    data_type: 'Data Type',
    length: 'Length',
    max_number: 'Max Number',
    decimal_part: 'Decimal Part',
    validation: 'Validation',
};
const tableColumnFieldValue = {
    table_name: 'test_table_name',
    column_name: 'test_column_name',
    column_name_alias: 'column_name_alias',
    data_type: 'varchar',
    length: 255,
    max_number: '',
    decimal_part: '',
    validation: '',
};
const datasourceFieldName = {
    source_name: 'Source Name',
    table_id: 'Table Id',
    starting_row_number: 'Starting Row Number',
};
const datasourceFieldValue = {
    source_name: 'datasource_name',
    table_id: 'test_table_name',
    starting_row_number: 2,
};
const datasourceColumnFieldName = {
    datasource_name: 'Datasource Name',
    column_index: 'Column Index',
    column_name: 'Column Name',
    table_column: 'Table Column',
};
const datasourceColumnFieldValue = {
    datasource_name: 'datasource_name',
    column_index: 'B',
    column_name: 'datasource_column_name',
    table_column: 'test_column_name',
};
const selectors = {
    // 左ペイン
    drawerButton: '.v-app-bar__nav-icon',
    // アップロード成功ポップアップ
    successPopUp: '.v-dialog',
    // New Itemボタン
    newItemBtn: 'button:contains("New Item")',
    // New Itemポップアップ
    newItemPopup: '.v-dialog:contains("New Item")',
    // Saveボタン
    saveButton: 'button:contains("Save")',
    // テーブル削除ボタン
    deleteBtn: '.mdi.mdi-delete',
    //Get snackbar
    snackbar: '.v-snack__content',
    // 追加成功を表すメッセージ
    CreateSuccessMsg: 'Created Successfully!',
    // 追加成功を表すメッセージ
    FetchSuccessMsg: 'Data Fetched Successfully!',
    // 削除成功を表すメッセージ
    deleteSuccessMsg: 'Deleted Successfully!',
    //Left pane item
    leftItemList: '.v-list-item__title',
};


describe('Initial Display', function () {
    it('Open admin window and check page content', function () {
        // Visit admin URL
        cy.visit(adminURL);

        //check hamburger button visibility.
        cy.get(selectors.drawerButton).should('visible');
        //Check title
        cy.get('.v-toolbar__content').find('#gens-logo').should('be.exist');
        cy.get('.v-toolbar__title').should('contain', 'for Nobels');
        //Check body content
        cy.get('.card-body').should('contain', 'I\'m GENS Admin');
    });

    it('Open admin window and check items from left pane', function () {
        // Visit admin URL
        cy.visit(adminURL);

        //open left pane
        cy.get(selectors.drawerButton).click();

        //Check item list and order from left pane
        cy.get(selectors.leftItemList).eq(0).should('contain', 'Table List');
        cy.get(selectors.leftItemList).eq(1).should('contain', 'Table Columns');
        cy.get(selectors.leftItemList).eq(2).should('contain', 'Datasource');
        cy.get(selectors.leftItemList).eq(3).should('contain', 'Datasource Columns');
        cy.get(selectors.leftItemList).eq(4).should('contain', 'Definition Upload');
    });
});


describe('Add operation for Tables', function () {
    it('Click on "Table List": Check page title', function () {
        cy.server();
        // Table List API.
        cy.route("get", getTableAPI).as('getTable');

        // Visit admin URL
        cy.visit(adminURL);
        //Open left pane
        cy.get(selectors.drawerButton).click();

        //Click on Table List
        cy.get(selectors.leftItemList).contains('Table List').click();

        // wait for table list api response
        cy.wait('@getTable');

        //Check title
        cy.get('.v-toolbar__title').should('contain', 'Table List');
    });

    it('Click on "New Item": Check form fields', function () {
        //Open form window
        cy.get(selectors.newItemBtn).click();

        // check form existence
        cy.get('.v-card__title').should('contain', formTitle.new);
        //Check field existance
        cy.get(selectors.newItemPopup).within(($form) => {
            cy.get('.v-input__slot').eq(0).should('contain', tableFieldName.table_name).should('be.visible');
            cy.get('.v-input__slot').eq(1).should('contain', tableFieldName.table_name_alias).should('be.visible');
        });
    });

    it('Click on "Save": Insert new item in Tables', function () {

        cy.server();
        // Table List API.
        cy.route("post", postTableAddAPI).as('postTableAddAPI');

        //Input form data
        cy.get(selectors.newItemPopup).find('.v-text-field__slot').eq(0).type(tableFieldData.table_name);
        cy.get(selectors.newItemPopup).find('.v-text-field__slot').eq(1).type(tableFieldData.table_name_alias);

        //左ペインを開く
        cy.get(selectors.saveButton).click();

        cy.wait('@postTableAddAPI', {
            timeout: 60000
        });

        //check form closing
        cy.get(selectors.newItemPopup).should('not.be', 'visible');
        //Check snacbar visibility and message
        cy.get(selectors.snackbar).should('contain', selectors.CreateSuccessMsg).should('be', 'visible');

        //check in datatable/specific place.
        cy.get('table').within(($table) => {
            cy.get('tr').eq(1).find('td').eq(1).should('contain', tableFieldData.table_name);
            cy.get('tr').eq(1).find('td').eq(2).should('contain', tableFieldData.table_name_alias);
        });
    });
});

describe('Add operation for Table Columns', function () {
    it('Click on "Table Columns": Check page title', function () {
        // Visit admin URL
        cy.visit(adminURL);
        //Open left pane
        cy.get(selectors.drawerButton).click();

        //Click on Table List
        cy.get(selectors.leftItemList).contains('Table Columns').click();

        //Check title
        cy.get('.v-toolbar__title').should('contain', 'Table Columns');
        //Check snackbar success message
        cy.get(selectors.snackbar).should('contain', selectors.FetchSuccessMsg).should('be', 'visible');
    });

    it('Click on "New Item": Check form fields', function () {
        //Open form window
        cy.get(selectors.newItemBtn).click();

        // check form existence
        cy.get('.v-card__title').should('contain', formTitle.new);
        //Check field existance
        cy.get(selectors.newItemPopup).within(($form) => {
            cy.get('.v-input__slot').eq(0).should('contain', tableColumnFieldName.table_name).should('be.visible');
            cy.get('.v-input__slot').eq(1).should('contain', tableColumnFieldName.column_name).should('be.visible');
            cy.get('.v-input__slot').eq(2).should('contain', tableColumnFieldName.column_name_alias).should('be.visible');
            cy.get('.v-input__slot').eq(3).should('contain', tableColumnFieldName.data_type).should('be.visible');
            cy.get('.v-input__slot').eq(4).should('contain', tableColumnFieldName.length).should('be.visible');
            cy.get('.v-input__slot').eq(5).should('contain', tableColumnFieldName.max_number).should('be.visible');
            cy.get('.v-input__slot').eq(6).should('contain', tableColumnFieldName.decimal_part).should('be.visible');
            cy.get('.v-input__slot').eq(7).should('contain', tableColumnFieldName.validation).should('be.visible');
        });
    });

    it('Click on "Save": Insert new item in Table Columns ', function () {

        cy.server();
        // Table List API.
        cy.route("post", postTableColumnAddAPI).as('postTableColumnAddAPI');

        //Input Form data
        cy.get(selectors.newItemPopup)
            .find('label:contains("Table Name")').parents('div[role="button"]').click();
        cy.get('.menuable__content__active').within(() => {
            cy.contains(tableColumnFieldValue.table_name).click();
        });
        cy.get(selectors.newItemPopup).find('.v-text-field__slot').eq(0).type(tableColumnFieldValue.column_name);
        cy.get(selectors.newItemPopup).find('.v-text-field__slot').eq(1).type(tableColumnFieldValue.column_name_alias);
        cy.get(selectors.newItemPopup)
            .find('label:contains("Data Type")').parents('div[role="button"]').click();
        cy.get('.menuable__content__active').within(() => {
            cy.contains('VARCHAR').click();
        });

        //Save data
        cy.get(selectors.newItemPopup).find(selectors.saveButton).click();
        //Wait for response
        cy.wait('@postTableColumnAddAPI', {
            timeout: 60000
        });

        //check form closing
        cy.get(selectors.newItemPopup).should('not.be', 'visible');
        //Check snacbar visibility and message
        cy.get(selectors.snackbar).should('contain', selectors.CreateSuccessMsg).should('be', 'visible');

        //check in datatable/specific place.
        cy.get('table').within(($table) => {
            cy.get('tr').eq(1).find('td').eq(0).should('contain', tableColumnFieldValue.table_name);
            cy.get('tr').eq(1).find('td').eq(1).should('contain', tableColumnFieldValue.column_name);
            cy.get('tr').eq(1).find('td').eq(2).should('contain', tableColumnFieldValue.column_name_alias);
            cy.get('tr').eq(1).find('td').eq(3).should('contain', tableColumnFieldValue.data_type);
            cy.get('tr').eq(1).find('td').eq(4).should('contain', tableColumnFieldValue.length);
            cy.get('tr').eq(1).find('td').eq(5).should('contain', tableColumnFieldValue.max_number);
            cy.get('tr').eq(1).find('td').eq(6).should('contain', tableColumnFieldValue.decimal_part);
            cy.get('tr').eq(1).find('td').eq(7).should('contain', tableColumnFieldValue.validation);
        });
    });
});

describe('Add operation for Datasource', function () {
    it('Click on "Datasource": Check page title', function () {
        cy.server();
        // Datasource API.
        cy.route("get", getDatasourceAPI).as('getDatasource');

        // Visit admin URL
        cy.visit(adminURL);
        //Open left pane
        cy.get(selectors.drawerButton).click();

        //Click on Datasource
        cy.get(selectors.leftItemList).contains('Datasource').click();

        // wait for Datasource api response
        cy.wait('@getDatasource');

        //Check title
        cy.get('.v-toolbar__title').should('contain', 'Datasource');
    });

    it('Click on "New Item": Check form fields', function () {
        //Open form window
        cy.get(selectors.newItemBtn).click();

        // check form existence
        cy.get('.v-card__title').should('contain', formTitle.new);
        //Check field existance
        cy.get(selectors.newItemPopup).within(($form) => {
            cy.get('.v-input__slot').eq(0).should('contain', datasourceFieldName.source_name).should('be.visible');
            cy.get('.v-input__slot').eq(1).should('contain', datasourceFieldName.table_id).should('be.visible');
            cy.get('.v-input__slot').eq(2).should('contain', datasourceFieldName.starting_row_number).should('be.visible');
        });
    });

    it('Click on "Save": Insert new item in Datasource', function () {
        cy.server();
        // Table List API.
        cy.route("post", postDatasourceAddAPI).as('postDatasourceAddAPI');

        //Input Form data
        cy.get(selectors.newItemPopup).find('.v-text-field__slot').eq(0).type(datasourceFieldValue.source_name);
        cy.get(selectors.newItemPopup)
            .find('label:contains("Table Id")').parents('div[role="button"]').click();
        cy.get('.menuable__content__active').within(() => {
            cy.contains(tableColumnFieldValue.table_name).click();
        });
        cy.get(selectors.newItemPopup).find('.v-text-field__slot').eq(1).type(datasourceFieldValue.starting_row_number);

        //左ペインを開く
        cy.get(selectors.saveButton).click();

        cy.wait('@postDatasourceAddAPI', {
            timeout: 60000
        });

        //check form closing
        cy.get(selectors.newItemPopup).should('not.be', 'visible');
        //Check snacbar visibility and message
        cy.get(selectors.snackbar).should('contain', selectors.CreateSuccessMsg).should('be', 'visible');

        //check in datatable/specific place.
        cy.get('table').within(($table) => {
            cy.get('tr').eq(1).find('td').eq(1).should('contain', datasourceFieldValue.source_name);
            cy.get('tr').eq(1).find('td').eq(3).should('contain', tableFieldData.table_name);
            cy.get('tr').eq(1).find('td').eq(4).should('contain', datasourceFieldValue.starting_row_number);
        });

    });
});

describe('Add operation for Datasource Columns', function () {
    it('Click on "Datasource Columns": Check page title', function () {
        // Visit admin URL
        cy.visit(adminURL);
        //Open left pane
        cy.get(selectors.drawerButton).click();

        //Click on Datasource Columns
        cy.get(selectors.leftItemList).contains('Datasource Columns').click();

        //Check title
        cy.get('.v-toolbar__title').should('contain', 'Datasource Columns');
        //Check snackbar success message
        cy.get(selectors.snackbar).should('contain', selectors.FetchSuccessMsg).should('be', 'visible');
    });

    it('Click on "New Item": Check form fields', function () {
        //Open form window
        cy.get(selectors.newItemBtn).click();

        // check form existence
        cy.get('.v-card__title').should('contain', formTitle.new);
        //Check field existance
        cy.get(selectors.newItemPopup).within(($form) => {
            cy.get('.v-input__slot').eq(0).should('contain', datasourceColumnFieldName.datasource_name).should('be.visible');
            cy.get('.v-input__slot').eq(1).should('contain', datasourceColumnFieldName.column_index).should('be.visible');
            cy.get('.v-input__slot').eq(2).should('contain', datasourceColumnFieldName.column_name).should('be.visible');
            cy.get('.v-input__slot').eq(3).should('contain', datasourceColumnFieldName.table_column).should('be.visible');
        });
    });

    it('Click on "Save": Insert new item in Datasource Columns', function () {
        cy.server();
        // Table List API.
        cy.route("post", postDatasourceColumnAddAPI).as('postDatasourceColumnAddAPI');

        //Input Form data
        cy.get(selectors.newItemPopup)
            .find('label:contains("Datasource Name")').parents('div[role="button"]').click();
        cy.get('.menuable__content__active').within(() => {
            cy.contains(datasourceColumnFieldValue.datasource_name).click();
        });
        cy.get(selectors.newItemPopup).find('.v-text-field__slot').eq(0).type(datasourceColumnFieldValue.column_index);
        cy.get(selectors.newItemPopup).find('.v-text-field__slot').eq(1).type(datasourceColumnFieldValue.column_name);

        cy.get(selectors.newItemPopup)
            .find('label:contains("Table Column")').parents('div[role="button"]').click();

        cy.get('.menuable__content__active').within(() => {
            cy.contains(datasourceColumnFieldValue.table_column).click();
        });

        //左ペインを開く
        cy.get(selectors.saveButton).click();

        cy.wait('@postDatasourceColumnAddAPI', {
            timeout: 60000
        });

        //check form closing
        cy.get(selectors.newItemPopup).should('not.be', 'visible');
        //Check snacbar visibility and message
        cy.get(selectors.snackbar).should('contain', selectors.CreateSuccessMsg).should('be', 'visible');

        //check in datatable/specific place.
        cy.get('table').within(($table) => {
            cy.get('tr').eq(1).find('td').eq(1).should('contain', datasourceColumnFieldValue.datasource_name);
            cy.get('tr').eq(1).find('td').eq(2).should('contain', datasourceColumnFieldValue.column_index);
            cy.get('tr').eq(1).find('td').eq(3).should('contain', datasourceColumnFieldValue.column_name);
            cy.get('tr').eq(1).find('td').eq(6).should('contain', datasourceColumnFieldValue.table_column);
        });

    });
});

describe('Datasource column Delete Operation', function () {
    it('Click on the Trash icon: Delete Datasource column', function () {
        cy.server();
        // Datasource List API.
        cy.route("get", getDatasourceColumnAPI).as('getDatasourceColumn');

        cy.route("post", postDatasourceColumnDeleteAPI).as('postDatasourceColumnDeleteAPI');

        // Visit admin URL
        cy.visit(adminURL);
        //Open left pane
        cy.get(selectors.drawerButton).click();

        //Click on Datasource List
        cy.get(selectors.leftItemList).contains('Datasource Columns').click();

        // wait for Datasource list api response
        cy.wait('@getDatasourceColumn');

        cy.contains(datasourceColumnFieldValue.datasource_name).parents('tr').within(() => {
            cy.get(selectors.deleteBtn).click();
        });

        cy.on('window:confirm', () => true);

        // response を受け取るまで待つ
        cy.wait('@postDatasourceColumnDeleteAPI', {
            timeout: 60000
        });
        //Check snackbar success message
        cy.get(selectors.snackbar).should('contain', selectors.deleteSuccessMsg).should('be', 'visible');
        //Check Datasource data
        cy.get('table').within(($table) => {
            cy.get('tr').eq(1).find('td').eq(0).should('not.contain', datasourceColumnFieldValue.datasource_name);
            cy.get('tr').eq(1).find('td').eq(1).should('not.contain', datasourceColumnFieldValue.column_index);
            cy.get('tr').eq(1).find('td').eq(2).should('not.contain', datasourceColumnFieldValue.column_name);
            cy.get('tr').eq(1).find('td').eq(3).should('not.contain', datasourceColumnFieldValue.table_column);
        });
    });
});

describe('Datasource Delete Operation', function () {
    it('Click on the Trash icon: Delete Datasource', function () {
        cy.server();
        // Datasource List API.
        cy.route("get", getDatasourceAPI).as('getDatasource');

        cy.route("post", postDatasourceDeleteAPI).as('postDatasourceDeleteAPI');

        // Visit admin URL
        cy.visit(adminURL);
        //Open left pane
        cy.get(selectors.drawerButton).click();

        //Click on Datasource List
        cy.get(selectors.leftItemList).contains('Datasource').click();

        // wait for Datasource list api response
        cy.wait('@getDatasource');

        cy.contains(datasourceFieldValue.source_name).parents('tr').within(() => {
            cy.get(selectors.deleteBtn).click();
        });

        cy.on('window:confirm', () => true);

        // response を受け取るまで待つ
        cy.wait('@postDatasourceDeleteAPI', {
            timeout: 60000
        });
        //Check snackbar success message
        cy.get(selectors.snackbar).should('contain', selectors.deleteSuccessMsg).should('be', 'visible');
        //Check Datasource data
        cy.get('table').within(($table) => {
            cy.get('tr').eq(1).find('td').eq(1).should('not.contain', datasourceFieldValue.source_name);
            cy.get('tr').eq(1).find('td').eq(2).should('not.contain', datasourceFieldValue.table_id);
            cy.get('tr').eq(1).find('td').eq(3).should('not.contain', datasourceFieldValue.starting_row_number);
        });
    });
});

describe('Table Column Delete Operation', function () {
    it('Click on the Trash icon: Delete Table column', function () {
        cy.server();
        // Table Columns List API.
        cy.route("get", getTableColumnsAPI).as('getTableColumns');

        cy.route("post", postTableColumnDeleteAPI).as('postTableColumnDeleteAPI');

        // Visit admin URL
        cy.visit(adminURL);
        //Open left pane
        cy.get(selectors.drawerButton).click();

        //Click on Table Columns List
        cy.get(selectors.leftItemList).contains('Table Columns').click();

        // wait for table list api response
        cy.wait('@getTableColumns');

        cy.contains(tableFieldData.table_name).parents('tr').within(() => {
            cy.get(selectors.deleteBtn)
                .click();
        });

        cy.on('window:confirm', () => true);

        // response を受け取るまで待つ
        cy.wait('@postTableColumnDeleteAPI', {
            timeout: 60000
        });
        //Check snackbar success message
        cy.get(selectors.snackbar).should('contain', selectors.deleteSuccessMsg).should('be', 'visible');
        //Check table data
        cy.get('table').within(($table) => {
            cy.get('tr').eq(1).find('td').eq(0).should('not.contain', tableColumnFieldValue.table_name);
            cy.get('tr').eq(1).find('td').eq(1).should('not.contain', tableColumnFieldValue.column_name);
            cy.get('tr').eq(1).find('td').eq(2).should('not.contain', tableColumnFieldValue.column_name_alias);
            cy.get('tr').eq(1).find('td').eq(3).should('not.contain', tableColumnFieldValue.data_type);
            cy.get('tr').eq(1).find('td').eq(4).should('not.contain', tableColumnFieldValue.length);
            cy.get('tr').eq(1).find('td').eq(5).should('not.contain', tableColumnFieldValue.max_number);
            cy.get('tr').eq(1).find('td').eq(6).should('not.contain', tableColumnFieldValue.decimal_part);
            cy.get('tr').eq(1).find('td').eq(7).should('not.contain', tableColumnFieldValue.validation);
        });
    });
});

describe('Table Delete Operation', function () {
    it('Click on the Trash icon: Delete Table', function () {
        cy.server();
        // Table List API.
        cy.route("get", getTableAPI).as('getTable');

        cy.route("post", postTableDeleteAPI).as('postTableDeleteAPI');

        // Visit admin URL
        cy.visit(adminURL);
        //Open left pane
        cy.get(selectors.drawerButton).click();

        //Click on Table List
        cy.get(selectors.leftItemList).contains('Table List').click();

        // wait for table list api response
        cy.wait('@getTable');

        cy.contains(tableFieldData.table_name).parents('tr').within(() => {
            cy.get(selectors.deleteBtn)
                .click();
        })

        cy.on('window:confirm', () => true);

        // response を受け取るまで待つ
        cy.wait('@postTableDeleteAPI', {
            timeout: 60000
        });
        //Check snackbar success message
        cy.get(selectors.snackbar).should('contain', selectors.deleteSuccessMsg).should('be', 'visible');
        //Check table data
        cy.get('table').within(($table) => {
            cy.get('tr').eq(1).find('td').eq(1).should('not.contain', tableFieldData.table_name);
            cy.get('tr').eq(1).find('td').eq(2).should('not.contain', tableFieldData.table_name_alias);
        });
    });
});
