<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Services\ServiceYClients;
use App\Services\ServiceAmoCRM;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{
    public function action()
    {
        //TODO delete??
        $arrayForClient = Client::buildArrayForModel(Request::capture()->toArray());

        $client = Client::updateOrCreate($arrayForClient);

        if($client->client_id)
            $yc_client = $this->YClients->getClient(
                $client->company_id,
                $client->client_id,
                env('YC_USER_TOKEN')
            );

        $client->fill($yc_client);
        $client->save();

        $arrayForRecord = Record::buildArrayForModel(Request::capture()->toArray());

        $record = Record::updateOrCreate($arrayForRecord);

        //поиск контакта
        //поиск сделок
        //создание сделки
        //создание контакта
        //обновление контакта
        //обновление сделки
    }

    public function deleteRecord($request)
    {
        Log::info(__METHOD__);

        $this->YClients->deleteRecord($request->post());
    }
}
