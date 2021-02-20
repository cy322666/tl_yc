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
Route::post('/record', function (Request $request) {

    switch ($request->post('resource')) {//TODO в одну строку

        case 'record' :
            //return Route::post('/record/index')->middleware('client');
            //return Redirect::route('record')->withInput(Request::capture()->post());
            return app('App\Http\Controllers\RecordController')->index($request);//->middleware('client'); //Call to a member function middleware()


        case 'finances_operation' :

            return Redirect::route('transaction');

        case 'goods_operations_sale' :

            return Redirect::route('abonement');
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
