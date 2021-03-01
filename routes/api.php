<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;

use App\Http\Controllers\RecordController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AbonementController;

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

/*
 * все уведомления от yclients
 */
Route::post('/yclients', function (Request $request) {

    switch ($request->post('resource')) {//TODO в одну строку

        case 'record' :

            return app('App\Http\Controllers\RecordController')->index($request);

        case 'finances_operation' :

            return app('App\Http\Controllers\TransactionController')->create($request);

        case 'goods_operations_sale' :

            return app('App\Http\Controllers\AbonementController')->create($request);
    }
});

Route::any('/record/index', 'App\Http\Controllers\RecordController@index')
    ->name('record')
    ->middleware('client');

Route::get('/transaction/create', [TransactionController::class, 'create'])
    ->name('transaction')
    ->middleware('record');

Route::get('/abonement/create', [AbonementController::class, 'create'])
    ->name('abonement')
    ->middleware('abonement');

/*
 * крон ожидания оплаты
 */
Route::post('/pay', [RecordController::class, 'pay']);
