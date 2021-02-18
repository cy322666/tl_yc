<?php

namespace App\Http\Controllers;


use App\Models\Record;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{
    public function index(Request $request)
    {
        if(empty($request->post('client')['id'])) exit;

        if($request->post('attendance')) {

            $client = Client::getClient();
            $record = Record::getRecord();

            switch ($request->post('attendance')) {

                case -1 :
                    $this->cancel($client, $record);

                    break;
                case 0 :
                    $this->wait($client, $record);

                    break;
                case 1 :
                    $this->came($client, $record);

                    break;
                case 2 :
                    $this->confirm($client, $record);

                    break;
            }
        }

    }

    public function wait(Client $client, Record $record)
    {
        $this->amoApi->updateOrCreate($client);
    }

    public function confirm(Client $client, Record $record)
    {

    }

    public function cancel(Client $client, Record $record)
    {

    }

    public function came(Client $client, Record $record)
    {

    }



    public function action()
    {
        /*
         * Работа с контактом - клиентом
         */


        if ($client->contact_id) {

            $contact = $this->amoClient->updateContact($client);
        } else {
            $contact = $this->amoClient->searchContact($client);

            if($contact)
                $contact = $this->amoClient->updateContact($client);
            else
                $contact = $this->amoClient->createContact($client);
        }
    }
}
