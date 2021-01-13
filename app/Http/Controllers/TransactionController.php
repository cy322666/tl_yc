<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ServiceYClients;
use App\Services\ServiceAmoCRM;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public $YClients;
    public $amoCRM;

    public function __construct()
    {
        $this->YClients = new ServiceYClients();
        $this->amoCRM   = new ServiceAmoCRM();
    }

    public function createTransaction(Request $request)
    {
        Log::info(__METHOD__);

        $this->YClients->createTransaction($request);
//        exit;
//        $errors = $this->validate($request, [
//            'data.*.good' => 'required',
//        ]);
//        Log::info(__METHOD__.' errors -> '.json_encode($errors));
//        if(count($errors) > 0) {
//            //нет названия в good
//            //обычная транзакция
//            Log::info(__METHOD__.' обычная оплата');
//            $this->YClients->createTransaction($request);
//        } else {
//            //есть название - это абонемент
//            Log::info(__METHOD__.' продажа абонемента');
//            return app('App\Http\Controllers\AbonementController')->createAbonement($request);
//        }
    }

    public function updateTransaction()
    {
        Log::info(__METHOD__);
    }

    public function deleteTransaction()
    {
        Log::info(__METHOD__);
    }
}