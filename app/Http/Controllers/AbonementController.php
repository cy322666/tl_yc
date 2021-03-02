<?php

namespace App\Http\Controllers;

use App\Models\Abonement;
use App\Models\Client;
use App\Models\Record;
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

            $client = Client::getClient();

            $contact = $this->amoApi->updateOrCreate($client);

            $client->contact_id = $contact->id;
            //$abonements = YClients::getAbonements(Client::where('client_id', 73168632)->first());

            $abonement = Abonement::getAbonement();

            $lead = $this->amoApi->createAbonement($client, $abonement);

            $abonement->lead_id = $lead->id;
            //TODO not save lead_id
            //TODO abonement in note no valid
            $abonement->save();
            $client->save();

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

    /**
     * Крон проверки посещений без оплат (по абонементу)
     */
    public function pay()
    {
        $records = Record::where('status', 'no_pay')->all();

        if($records) {

            foreach ($records as $record) {

                $datetime5Hours = $now - '5 hours';
                $datetime6Hours = $now - '6 hours';

                if ($record->datetime > $datetime5Hours &&
                    $record->datetime < $datetime6Hours) {

                    //значит это посещение по абонементу
                    $abonements = $record->abonements;
//TODO запросить все активные абоны юзера и соотнеси к тем, что в бд?
                    foreach ($abonements as $abonement) {

                        if($abonement->is_active = true) {
                            //TODO add field is_active

                        }
                    }
                }
            }
        }
    }
}
