<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/record', function (Request $request) {

    switch ($request->post('resource')) {

        case 'record' :

            Route::post('/record', [RecordController::class, 'index'])->middleware('CheckClient');
            break;

        case 'finances_operation' :

            Route::post('/record', [TransactionController::class, 'create'])->middleware('CheckRecord');
            break;

        case 'goods_operations_sale' :

            Route::post('/record', [AbonementController::class, 'create'])->middleware('CheckAbonement');
            break;
    }
});




/*
 * крон ожидания оплаты
 */
Route::post('/pay', [RecordController::class, 'pay']);

Route::any('/transaction/create', 'TransactionController@createTransaction');
