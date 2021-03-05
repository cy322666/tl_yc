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
    public function create(Request $request)
    {
        $request = $request::capture()->toArray();

        if(Abonement::checkName($request['data']['good']['title'])) {

            $client = Client::getClient();

            $contact = $this->amoApi->updateOrCreate($client);

            $client->contact_id = $contact->id;
            $client->save();

            $abonement = Abonement::getAbonement();

            $lead = $this->amoApi->createAbonement($client, $abonement);

            unset($abonement);
            $abonement = Abonement::find($request['data']['id']);

            $abonement->lead_id = $lead->id;
            $abonement->save();

            $this->amoApi->createNoteLeadAbonement($abonement);
        }
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
dd($abonements);
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
