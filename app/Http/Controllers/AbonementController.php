<?php

namespace App\Http\Controllers;

use App\Models\Abonement;
use App\Models\Client;
use App\Models\Record;
use App\Services\YClients;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbonementController extends Controller
{
    //TODO миддлваря на проверку продажи абона/наличие буковок
    public function create(Request $request)
    {
        $request = $request::capture()->toArray();

        if(Abonement::checkName($request['data']['good']['title'])) {

            $client = Client::getClient();

            $contact = $this->amoApi->updateOrCreate($client);

            $client->contact_id = $contact->id;

            /*
             *
             *
             *
             */
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
        $records = Record::where('status', 'no_pay')
            ->where('datetime', '>', date('Y-m-d H:i:s', strtotime("-6 hours")))
            ->where('datetime', '<', date('Y-m-d H:i:s', strtotime("-5 hours")))
            ->get();

        if($records) {

            foreach ($records as $record) {

                $client = $record->client;

                $abonements = YClients::getAbonements($client);

                if(!empty($abonements['data'][0])) {
                    //TODO add field code (number)
                    //TODO update contact??
                    foreach ($abonements['data'] as $abonementArray) {

                        if($abonementArray['status']['title'] == 'Активирован' &&
                           Abonement::checkName($abonementArray['type']['title'])) {

                            $abonementModel = Abonement::firstOrCreate([
                                'company_id' => $record->company_id,
                                'abonement_id' => $abonementArray['id'],
                                'title' => $abonementArray['type']['title'],
                                'client_id' => $client->client_id,
                                'cost' => $abonementArray['default_balance'],
                                'sale' => Abonement::getSaleByTitle($abonementArray['type']['title']),
                            ]);

                            if(!$abonementModel->lead_id)

                                $lead = $this->amoApi->createAbonement($client, $abonementModel);

                                $abonementModel->lead_id = $lead->id;
                                $abonementModel->save();
                            }

                            if($abonementModel->amount > $abonementArray['balance']) {

                                //посещение по этому абону
                                //выполняем действия
                                //$abon->balance = $abonementArray['balance'];
                                //конфликт с созданием сделки
                            }

                            $lead = $this->amoApi->updateLeadAbonement($abonementModel);

                            //$this->amoApi->createNoteLeadAbonement($abonementModel);
                            //TODO не подходит примечание
                        }
                    }
                    if($abonementModel->amount <= 5000)

                        $this->amoApi->updateStatus($record, env('STATUS_FINISH'));
                    //TODO status abonement is_active = false?
            }
        }
    }
}
