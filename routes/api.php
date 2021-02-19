<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecordController;

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
 * работа по записи
 */
Route::post('/record', [RecordController::class, 'index']);

/*
 * крон ожидания оплаты
 */
Route::post('/pay', [RecordController::class, 'pay']);

Route::any('/transaction/create', 'TransactionController@createTransaction');
