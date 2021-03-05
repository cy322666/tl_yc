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

            $abonements = YClients::getAbonements($client);

            if(!empty($abonements['data'][0])) {

                //TODO update contact??
                foreach ($abonements['data'] as $abonementArray) {

                    if ($abonementArray['status']['title'] == 'Активирован' &&
                        Abonement::checkName($abonementArray['type']['title'])) {

                        $saleFact = Abonement::getCostByTitle($abonementArray['type']['title']);

                        $balance = $abonementArray['balance'] > $saleFact ? $saleFact : $abonementArray['balance'];

                        $arrayFields = [
                            'company_id' => $client->company_id,
                            'abonement_id' => $abonementArray['id'],
                            'title' => $abonementArray['type']['title'],
                            'client_id' => $client->client_id,
                            'cost' => $saleFact,
                            'is_active' => 1,
                            'balance' => $abonementArray['balance'],
                            'sale' => $balance,
                        ];
                        //TODO cost and sale balanse??
                        $abonementModel = Abonement::find($abonementArray['id']);

                        if ($abonementModel == null) {

                            $abonementModel = Abonement::create($arrayFields);

                            $abonementLead = $this->amoApi->createAbonement($client, $abonementModel);

                            $abonementModel = Abonement::find($abonementArray['id']);

                            $abonementModel->lead_id = $abonementLead->id;
                            $abonementModel->save();
                            //TODO dont save lead_id
                            //TODO dont save balance in lead
                        } else {

                            $abonementModel->fill($arrayFields);

                            $abonementLead = $this->amoApi->updateAbonement($abonementModel);
                        }

                        if ($abonementModel->balance > $abonementArray['balance'])

                            $this->amoApi->createNoteLeadAbonementPay($abonementModel);

                        if ($abonementModel->balance <= 5000)

                            $this->amoApi->updateStatus($abonementLead, env('STATUS_FINISH'));
                        //TODO status abonement is_active = false?

                        unset($abonementModel);
                        unset($abonementLead);
                    }
                }
            }
        }
    }

    /**
     * Крон проверки посещений без оплат (по абонементу)
     */
    public function pay()
    {//dd(date('Y-m-d H:i:s', strtotime("-6 hours")));
//        $records = Record::where('status', 'no_pay')
//            ->where('datetime', '>', date('Y-m-d H:i:s', strtotime("-6 hours")))
//            ->where('datetime', '<', date('Y-m-d H:i:s', strtotime("-5 hours")))
//            ->get();

        $records = Record::all();

        if($records) {

            foreach ($records as $record) {

                $client = $record->client;

                $abonements = YClients::getAbonements($client);

                if(!empty($abonements['data'][0])) {

                    //TODO update contact??
                    foreach ($abonements['data'] as $abonementArray) {

                        if ($abonementArray['status']['title'] == 'Активирован' &&
                            Abonement::checkName($abonementArray['type']['title'])) {

                            $saleFact = Abonement::getCostByTitle($abonementArray['type']['title']);

                            $balance = $abonementArray['balance'] > $saleFact ? $saleFact : $abonementArray['balance'];

                            $arrayFields = [
                                'company_id' => $record->company_id,
                                'abonement_id' => $abonementArray['id'],
                                'title' => $abonementArray['type']['title'],
                                'client_id' => $client->client_id,
                                'cost' => $saleFact,
                                'is_active' => 1,
                                'balance' => $abonementArray['balance'],
                                'sale' => $balance,
                            ];
                            //TODO cost and sale balanse??
                            $abonementModel = Abonement::find($abonementArray['id']);

                            if ($abonementModel == null) {

                                $abonementModel = Abonement::create($arrayFields);

                                $abonementLead = $this->amoApi->createAbonement($client, $abonementModel);

                                $abonementModel = Abonement::find($abonementArray['id']);

                                $abonementModel->lead_id = $abonementLead->id;
                                $abonementModel->save();
                                //TODO dont save lead_id
                                //TODO dont save balance in lead
                            } else {

                                $abonementModel->fill($arrayFields);

                                $abonementLead = $this->amoApi->updateAbonement($abonementModel);
                            }

                            if ($abonementModel->balance > $abonementArray['balance'])

                                $this->amoApi->createNoteLeadAbonementPay($abonementModel);

                            if ($abonementModel->balance <= 5000)

                                $this->amoApi->updateStatus($abonementLead, env('STATUS_FINISH'));
                            //TODO status abonement is_active = false?

                            $record->status = 'payed';
                            $record->save();

                            unset($abonementModel);
                            unset($abonementLead);
                        }
                    }
                }
            }
        }
    }
}
