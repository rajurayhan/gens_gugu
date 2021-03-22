<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['api'], 'prefix' => 'v1'], function () {
    Route::get('/excel_files', 'FileController@getFileList');
    Route::delete('/excel_files/{file_id?}', 'FileController@deleteFile');
    Route::get('/datasources', 'DataSourceController@getDataSourceList');

    //bulk upload definition route
    Route::post('/definition-bulk', 'APIUploadDefinitionBulkController@store');

    // TableData Routes
    Route::get('/table/{id}', 'APITableDataController@getTableInfo');
    Route::get('/table-data/{id}', 'APITableDataController@getTableData');
});

Route::fallback(function () {
    return response()->json([
        'message' => 'API Endpoint not found.'
    ], 404);
}); // Fallback API route

// Table Definitions Routes
Route::get('/v1/table-columns', 'APITableColumnsController@index')->name('getTableColumns');
Route::post('/v1/update/table-columns', 'APITableColumnsController@update')->name('updateTableColumns');
Route::post('/v1/add/table-columns', 'APITableColumnsController@add')->name('addTableColumns');
Route::post('/v1/delete/table-columns', 'APITableColumnsController@delete')->name('deleteTableColumns');

// Tables Routes
Route::get('/v1/tables', 'APITablesController@index')->name('getTables');
Route::post('/v1/add/tables', 'APITablesController@add')->name('addTable');
Route::post('/v1/update/tables', 'APITablesController@update')->name('updateTable');
Route::get('/v1/confirm-relation/tables', 'APITablesController@confirmRelation')->name('confirmTableDeletion');
Route::post('/v1/delete/tables', 'APITablesController@delete')->name('deleteTable');

//m_data_source routes.....
Route::get('/get/data-source/all', 'DataSourceController@getDataSource')->name('getData');
Route::post('/update/data-source', 'DataSourceController@update')->name('updateDataSource');
Route::post('/add/data-source', 'DataSourceController@add')->name('addDataSource');
Route::post('/delete/data-source', 'DataSourceController@delete')->name('deleteDataSource');

//m_data_column_mapping routes.....
Route::get('/get/table-columns/{id}', 'DatasourceColumnsController@getTableColumns')->name('getTableColumnsData');
Route::get('/get/table-id-datasource/{id}', 'DatasourceColumnsController@getTableIdOfDataSource')->name('getTableIdOfDataSource');
Route::get('/get/datasource-columns', 'DatasourceColumnsController@getDatasourceColumns')->name('getDataColumnData');
Route::post('/update/datasource-columns', 'DatasourceColumnsController@update')->name('updateDataColumn');
Route::post('/add/datasource-columns', 'DatasourceColumnsController@add')->name('addDataColumn');
Route::post('/delete/datasource-columns', 'DatasourceColumnsController@delete')->name('deleteDataColumn');
