<?php

namespace App\Http\Controllers;

use App\Services\ServiceAmoCRM;
use App\Services\ServiceYClients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbonementController extends Controller
{
    public $YClients;
    public $amoCRM;

    public function __construct()
    {
        $this->YClients = new ServiceYClients();
        $this->amoCRM   = new ServiceAmoCRM();
    }

    public function createAbonement($request)
    {
        Log::info(__METHOD__);
        //$arrayAbonement = json_decode(file_get_contents(__DIR__ . '/AbonementCreate.json'), true);

        if(strripos($request['data']['good']['title'], 'ДК_') === false &&
            strripos($request['data']['good']['title'], 'С_') === false) {
            Log::info(__METHOD__.' _ не найдено ДК_ или С_ , название -> '.$request['data']['good']['title']);
            $this->YClients->createTransaction($request);
        } else {
            $this->YClients->createAbonement($request);
        }
    }
}
