<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{
    private $array_status = [];

    /**
     * @param Request $request
     *
     * получаем модели клиента и записи
     * вызываем экшен события
     */
    public function index(Request $request)
    {
        $client = Client::getClient();
        $record = Record::getRecord();
                                            //TODO если attendance равен тому шо в бд, то это обновление
        $requestArray = $request->toArray();

        if($requestArray['status'] == 'delete') $status = 3;
        else
            $status = $requestArray['data']['attendance'];

        $this->array_status = Record::getStatus($status);

        $action = $this->array_status['action'];

        $this->$action($client, $record);
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * клиент записан
     */
    public function wait(Client $client, Record $record)
    {
        $contact = $this->amoApi->updateOrCreate($client);

        $client->contact_id = $contact->id;

        $lead = $this->amoApi->searchOrCreate($client, $record);

        $record->lead_id = $lead->id;

        $this->amoApi->updateLead($record);

        $lead = $this->amoApi->updateStatus($record, $this->array_status['status_id']);

        $this->amoApi->createNoteLead($record, 'wait');

        $record->save();
        $client->save();
    }

    public function confirm(Client $client, Record $record)
    {
        $contact = $this->amoApi->updateOrCreate($client);

        $client->contact_id = $contact->id;

        $lead = $this->amoApi->searchOrCreate($client, $record);

        $record->lead_id = $lead->id;

        $this->amoApi->updateLead($record);

        $lead = $this->amoApi->updateStatus($record, $this->array_status['status_id']);

        $this->amoApi->createNoteLead($record, 'confirm');

        $record->save();
        $client->save();
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * Запись отменена
     */
    public function cancel(Client $client, Record $record)
    {
        $contact = $this->amoApi->updateOrCreate($client);

        $client->contact_id = $contact->id;

        $lead = $this->amoApi->searchOrCreate($client, $record);

        $record->lead_id = $lead->id;

        $this->amoApi->updateLead($record);

        $lead = $this->amoApi->updateStatus($record, $this->array_status['status_id']);

        $this->amoApi->createNoteLead($record, 'cancel');

        $record->save();
        $client->save();
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * Запись удалена
     */
    public function delete(Client $client, Record $record)
    {
        $this->amoApi->updateOrCreate($client);

        if($record->lead_id) {

            $this->amoApi->updateStatus($record, $this->array_status['status_id']);

            $this->amoApi->createNoteLeadDelete($record);
        }
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * Пришел по записи
     */
    public function came(Client $client, Record $record)
    {
        $contact = $this->amoApi->updateOrCreate($client);

        $client->contact_id = $contact->id;

        $lead = $this->amoApi->searchOrCreate($client, $record);

        $record->lead_id = $lead->id;

        $this->amoApi->updateLead($record);

        $lead = $this->amoApi->updateStatus($record, $this->array_status['status_id']);

        $this->amoApi->createNoteLead($record, 'came');

        $record->save();
        $client->save();
    }
    //TODO миддлваря на проверку нужного филиала
    //TODO миддлваря на проверку события
}
