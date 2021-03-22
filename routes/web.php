<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
});

Route::post('/upload-excel', 'UploadedFileController@store')->name('uploadFile');


// Master maintenance
Route::get('/admin', function () {
    return view('admin');
});
Route::get('/admin/{any}', function () {
    return view('admin');
})->where('any', '.*');

//table data
Route::get('/tabledata', function () {
    return view('tabledata');
});
Route::get('/tabledata/{any}', function () {
    return view('tabledata');
})->where('any', '.*');

// Fileupload and Filelist
Route::get('/{file}', function () {
    return view('main');
})->where('file', 'fileupload|filelist');

// Other: redirect to "/" (Dashboard)
Route::get('/{any}', function () {
    return redirect('/');
})->where('any', '.*');
