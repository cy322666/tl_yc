<?php

namespace App\Http\Controllers;

use App\Abonement;
use App\Models\Client;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbonementController extends Controller
{

    //TODO миддлваря на проверку продажи абона
    public function create(Request $request)
    {
//        if(strripos($request['data']['good']['title'], 'ДК_') === false &&
//           strripos($request['data']['good']['title'], 'С_') === false) {
//
//            Log::info(__METHOD__.' _ не найдено ДК_ или С_ , название -> '.$request['data']['good']['title']);

            //$this->YClients->createTransaction($request);
            //TODO контроллер транзакций

        $client = Client::getClient();

        $abonement = Abonement::getAbonement();

        $this->amoApi->updateOrCreate($client);

        $this->amoApi->createAbonement($client, $abonement);

        $this->amoApi->updateLead($abonement);

        $this->amoApi->createNoteLead($abonement, 'abonement');
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
