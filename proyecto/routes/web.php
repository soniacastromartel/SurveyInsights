<?php

use Illuminate\Support\Facades\Route;

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

// Route::get('/', function () {
//     return view('welcome');
// });


// Route::get('/', function () {
//      return view('surveys', ['title' => 'ENCUESTAS']);
// });

Route::get('/', 'App\Http\Controllers\SurveyController@index')->name('survey.index');
Route::post('/survey/calculate', 'App\Http\Controllers\SurveyController@calculate')->name('survey.calculate');
Route::get('/survey/preview', 'App\Http\Controllers\SurveyController@preview')->name('survey.preview');
Route::get('/survey/download', 'App\Http\Controllers\SurveyController@download')->name('survey.download');
Route::get('/survey/view', 'App\Http\Controllers\SurveyController@view')->name('survey.view');
Route::get('/survey/welcome', 'App\Http\Controllers\SurveyController@welcome')->name('survey.welcome');
Route::get('/survey/mail', 'App\Http\Controllers\SurveyController@mail')->name('survey.mail');
Route::get('/survey/getCompanies', 'App\Http\Controllers\SurveyController@getCompanies')->name('survey.getCompanies');




Route::get('/views/header', function() {
    return view('header');
})->name('header');

