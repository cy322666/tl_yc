<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{
    private $array_status = [];

    public function index(Request $request)
    {
        $client = Client::getClient();
        $record = Record::getRecord();
//TODO если attendance равен тому шо в бд, то это обновление
        $requestArray = $request->toArray();

        if($requestArray['status'] == 'delete') $status = 3;

        if($requestArray['data']['attendance']) {

            $status = $requestArray['data']['attendance'];
        }
        $status = 0;//TODO ???
        $this->array_status = Record::getStatus($status);

        $action = $this->array_status['action'];

        $this->$action($client, $record);
    }

    public function wait(Client $client, Record $record)
    {
        $this->amoApi->updateOrCreate($client);

        $this->amoApi->searchOrCreate($client, $record);

        $this->amoApi->updateLead($record);

        $this->amoApi->createNoteLead($record, 'wait');
    }

    public function confirm(Client $client, Record $record)
    {
        $this->amoApi->updateOrCreate($client);

        $this->amoApi->searchOrCreate($client, $record);

        $this->amoApi->updateStatus($record, $this->array_status['status_id']);

        $this->amoApi->updateLead($record);

        $this->amoApi->createNoteLead($record, 'confirm');
    }

    /**
     * @param Client $client
     * @param Record $record
     *
     * Запись отменена
     */
    public function cancel(Client $client, Record $record)
    {
        //TODO запись в модели??
        $this->amoApi->updateOrCreate($client);

        $this->amoApi->searchOrCreate($client, $record);

        $this->amoApi->updateStatus($record, $this->array_status['status_id']);

        $this->amoApi->updateLead($record);

        $this->amoApi->createNoteLead($record, 'cancel');
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

            $this->amoApi->updateStatus($record, 3);

            $this->amoApi->createNoteLead($record, 'delete');//TODO написать метод
        }
    }

    public function came(Client $client, Record $record)
    {
        $this->amoApi->updateOrCreate($client);

        $this->amoApi->searchOrCreate($client, $record);

        $this->amoApi->updateStatus($record, $this->array_status['status_id']);

        $this->amoApi->updateLead($record);
        //TODO статус -> wait
        $this->amoApi->createNoteLead($record, 'came');
    }

    //TODO миддлваря на проверку нужного филиала
    //TODO миддлваря на проверку события
    public function pay()
    {
//        $records = Record::where('attendance', '1')
//            ->where('=', 'status', 'wait_pay')//разница в 5 часах
//            -all();
        //проверка оплаты у записей
        //каждый час
        //отбор по attendance и статус
    }
}
