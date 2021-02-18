<?php

namespace App\Http\Controllers;

use App\Models\amoCRM;
use App\Models\Client;
use App\Services\ServiceAmoCRM;
use App\Services\ServiceYClients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbonementController extends Controller
{
    public $amoClient;

    public function __construct()
    {
        $this->amoClient = new amoCRM();
    }

    public function createAbonement($request)
    {
        Log::info(__METHOD__);

        if(strripos($request['data']['good']['title'], 'ДК_') === false &&
            strripos($request['data']['good']['title'], 'С_') === false) {

            Log::info(__METHOD__.' _ не найдено ДК_ или С_ , название -> '.$request['data']['good']['title']);

            //$this->YClients->createTransaction($request);
            //TODO контроллер транзакций

        } else {
            /*
         * Работа с контактом - клиентом
         */
            $arrayForClient = Client::buildArrayForModel(Request::capture()->toArray());

            $client = Client::updateOrCreate($arrayForClient);

            if ($client->client_id) {

                $yc_client = $this->YClients->getClient(
                    $client->company_id,
                    $client->client_id,
                    env('YC_USER_TOKEN')
                );
                $client->fill($yc_client);
                $client->save();
            }

            if ($client->contact_id)

                $contact = $this->amoClient->updateContact($client);
            else {
                $contact = $this->amoClient->searchContact($client);

                if($contact)
                    $contact = $this->amoClient->updateContact($client);
                else
                    $contact = $this->amoClient->createContact($client);
            }
            /*
             * Работа со сделкой -  абонементом
             */
            /*
             * При создании сделки с покупкой абонемента нам важно передавать следующие данные:
Стоимость операции (покупки)  → тянем в поле “бюджет сделки”
Информация по фактическому балансу после покупки ( берем из title) → Тянем в поле “Фактический баланс” (id 1104351)

            Также в сделке есть поле “Остаток на балансе” (id 1173769) это поле нужно сделать калькулируемым автоматически по следующей логике:
Начальное значение = полю “Фактический баланс”
Далее, при обновлении фактического баланса абонемента на стороне yc мы должны автоматически обновлять и данное поле.

             */
        }
    }
}
