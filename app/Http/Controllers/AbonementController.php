<?php

namespace App\Http\Controllers;

use App\Models\Abonement;
use App\Models\Client;
use App\Services\YClients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbonementController extends Controller
{
    //TODO миддлваря на проверку продажи абона/наличие буковок
    public function create(Request $request)
    {
        $request = $request::capture()->toArray();

        if(strripos($request['data']['good']['title'], 'ДК_') !== false ||
           strripos($request['data']['good']['title'], 'С_') !== false) {

//            $client = Client::getClient();
//
//            $contact = $this->amoApi->updateOrCreate($client);
//
//            $client->contact_id = $contact->id;
            $abonements = YClients::getAbonements(Client::where('client_id', 73168632)->first());

            dd($abonements);
            $abonement = Abonement::getAbonement();

            $contact = $this->amoApi->updateOrCreate($client);

            $this->amoApi->createAbonement($client, $abonement);

            $this->amoApi->updateLead($abonement);

            $this->amoApi->createNoteLeadAbonement($abonement);
        }


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
