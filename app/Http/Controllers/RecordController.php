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
        $client = Client::getClient();
        $record = Record::getRecord();

        if($request->post('status') == 'delete') $this->delete($client, $record);

        if($request->post('attendance')) {

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

        $this->amoApi->searchOrCreate($client, $record);

        $this->amoApi->updateStatus($record, $this->amoApi->getStatus($record->attendance));

        $this->amoApi->updateLead($record);

        $this->amoApi->createNoteLead($record, 'wait');
    }

    public function confirm(Client $client, Record $record)
    {
        $this->amoApi->updateOrCreate($client);

        $this->amoApi->searchOrCreate($client, $record);

        $this->amoApi->updateStatus($record, $this->amoApi->getStatus($record->attendance));

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

        $this->amoApi->updateStatus($record, $this->amoApi->getStatus($record->attendance));

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

        $this->amoApi->updateStatus($record, $this->amoApi->getStatus($record->attendance));

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
