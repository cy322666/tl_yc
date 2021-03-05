<?php

namespace App\Services;

use App\Models\Abonement;
use App\Models\Client;
use App\Models\Record;
use App\Models\Transaction;
use Ufee\Amo\Oauthapi;

class amoCRM
{
    public $amoApi;

    public function __construct()
    {
        $this->amoApi = \Ufee\Amo\Oauthapi::setInstance([
            'domain' => env('AMO_SUBDOMAIN'),
            'client_id' => env('AMO_CLIENT_ID'),
            'client_secret' => env('AMO_CLIENT_SECRET'),
            'redirect_uri' => env('AMO_REDIRECT_URL'),
        ]);
        //$oauth = $this->amoApi->fetchAccessToken(env('AMO_AUTH_CODE'));
        $this->amoApi = Oauthapi::getInstance(env('AMO_CLIENT_ID'));

        $this->amoApi->queries->cachePath(storage_path('cache/amocrm'));
        $this->amoApi->queries->logs(storage_path('logs/amocrm'));

        $this->amoApi->queries->setDelay(0.5);
    }

    public function getLead($id)
    {
        $lead = $this->amoApi->leads()->find($id);

        return $lead;
    }
    public function updateOrCreate(Client $client)
    {
        if($client->contact_id)

            $contact = $this->updateContact($client);
        else {
            $contact = $this->searchContact($client);

            if(!$contact)
                $contact = $this->createContact($client);
        }

        return $contact;
    }

    private function searchContact(Client $client)
    {
        if($client->phone)
            $contacts = $this->amoApi->contacts()->searchByPhone($client->phone);

        if(!$contacts->first() && $client->email)
            $contacts = $this->amoApi->contacts()->searchByEmail($client->email);

        $contact = $contacts->first() ? $contacts->first() : null;//TODO тернарный

        return $contact;
    }

    public function createLead(Client $client, Record $record, int $status_id = null)
    {
        $lead = $this->amoApi->leads()->create();

        $lead->name = 'Запись в YClients';

        $lead->contacts_id = $client->contact_id;

        if($status_id)
            $lead->status_id = $status_id;
        else
            $lead->status_id = Record::getStatus($record->attendance)['status_id'];

        $lead->save();

        return $lead;
    }

    public function createAbonement(Client $client, Abonement $abonement)
    {
        $lead = $this->amoApi->leads()->create();

        $lead->name = 'Абонемент в YClients';
        $lead->sale = $abonement->cost;
        $lead->contacts_id = $client->contact_id;
        $lead->status_id = env('STATUS_ABONEMENT');

        $lead->cf('Фактический баланс')->setValue($abonement->cost);
        $lead->cf('Остаток на балансе')->setValue($abonement->balance);
        $lead->cf('Салон')->setValue(Record::getFilial($abonement->company_id));

        $lead->save();

        return $lead;
    }

    public function updateAbonement(Abonement $abonement)
    {
        if($abonement->lead_id) {

            $lead = $this->amoApi->leads()->find($abonement->lead_id);

            $lead->sale = $abonement->sale;

            $lead->cf('Салон')->setValue(Record::getFilial($abonement->company_id));
            $lead->cf('Фактический баланс')->setValue($abonement->cost);
            $lead->cf('Остаток на балансе')->setValue($abonement->balance);

            $lead->save();

            return $lead;

        } else
            return null;
    }

    public function updateLead(Record $record)
    {
        if($record->lead_id) {

            $lead = $this->amoApi->leads()->find($record->lead_id);

            $lead->sale = $record->cost;

            $lead->cf('Салон')->setValue(Record::getFilial($record->company_id));
            $lead->cf('ID записи, Yclients')->setValue($record->record_id);
            $lead->cf('Дата и время записи, YClients')->setValue($record->datetime);

            //TODO fields
            $lead->save();

            return $lead;

        } else
            return null;
    }

    public function updateStatus($lead, int $status_id)
    {
        $lead->status_id = $status_id;
        $lead->save();

        return $lead;
    }

    private function createContact(Client $client)
    {
        $contact = $this->amoApi->contacts()->create();

        $contact->name = $client->name;
        $contact->cf('Email')->setValue($client->email);
        $contact->cf('Телефон')->setValue($client->phone, 'Home');

        $contact->save();

        $client->contact_id = $contact->id;
        $client->save();

        return $contact;
    }

    public function createTask(Record $record)
    {
        $task = $this->amoApi->createTask($type = 1);

        $task->text = 'Клиент оставил повторную заявку на сайте';
        $task->element_type = 2;
        //$task->responsible_user_id = $record->lead_id->responsible_user_id;
        $task->complete_till_at = strtotime('tomorrow');
        $task->element_id = $record->lead_id;
        $task->save();

        return $task;
    }

    public function searchOrCreate(Client $client, Record $record)
    {
        if(!$record->lead_id) {

            $lead = $this->searchLead($client, env('FIRST_PIPELINE'));

            if ($lead == null || ($lead->status_id == 142 || $lead->status_id == 143))

                $lead = $this->searchLead($client, env('SECOND_PIPELINE'));

            if ($lead) {

                if ($lead->status_id == 142 || $lead->status_id == 143)
                    //если закрытая в 1 воронке
                    $lead = $this->createLead($client, $record, self::pipelineHelper(env('SECOND_PIPELINE'), $record));

                elseif(Record::where('lead_id', $lead->id)->first())

                    $lead = $this->createLead($client, $record, self::pipelineHelper(env('SECOND_PIPELINE'), $record));
                else
                    $lead = $this->updateStatus($lead, self::pipelineHelper($lead->pipeline_id, $record));
            }

            if(!$lead)
                $lead = $this->createLead($client, $record, env('FIRST_PIPELINE'));

        } else {
            $lead = $this->amoApi->leads()->find($record->lead_id);

            $lead = $this->updateStatus($lead, self::pipelineHelper($lead->pipeline_id, $record));
        }

        return $lead;
    }

    public static function pipelineHelper(int $id, Record $record = null)
    {
        switch ($id) {

            case env('FIRST_PIPELINE') :

                switch ($record->attendance) {

                    case -1 :
                        return env('STATUS_CANCEL');
                    case 0 :
                        return env('STATUS_WAIT');
                    case 1 :
                        return env('STATUS_CAME');
                    case 2 :
                        return env('STATUS_CONFIRM');
                    default :
                        return env('STATUS_WAIT');
                }

            case env('SECOND_PIPELINE') :

                switch ($record->attendance) {

                    case -1 :
                        return env('STATUS2_CANCEL');
                    case 0 :
                        return env('STATUS2_WAIT');
                    case 1 :
                        return env('STATUS2_CAME');
                    case 2 :
                        return env('STATUS2_CONFIRM');
                    default :
                        return env('STATUS2_WAIT');
                }
        }
    }

    /**
     * @param Client $client
     * @param int $pipeline_id
     * @return null
     * @throws \Exception
     *
     * Поиск сделки в воронке у контакта
     */
    private function searchLead(Client $client, int $pipeline_id)
    {
        $contact = $this->amoApi->contacts()->find($client->contact_id);

        $leads = $contact->leads;

        if($leads) {
            foreach ($leads->toArray() as $arrayLead) {

                if ($arrayLead['pipeline_id'] == $pipeline_id) {

                    if($arrayLead['status_id'] != 142 && $arrayLead['status_id'] != 143)

                        return $this->amoApi->leads()->find($arrayLead['id']);

                    $lead = $this->amoApi->leads()->find($arrayLead['id']);
                }
            }

            if($lead) return $lead;

        } else
            return null;
    }

    private function updateContact(Client $client)
    {
        $contact = $this->amoApi->contacts()->find($client->contact_id);

        $contact->cf('Телефон')->setValue($client->phone, 'Work');
        $contact->cf('Email')->setValue($client->email);
        $contact->save();

        return $contact;
    }

    public function createNoteLeadTransaction(Transaction $transaction, Record $record)
    {
        $lead = $this->amoApi->leads()->find($record->lead_id);

        $note = $lead->createNote($type = 4);

        $note->text = self::createArrayNoteTextPay($transaction, $record);
        $note->element_type = 2;
        $note->element_id = $record->lead_id;
        $note->save();

        return $note;
    }

    private function createArrayNoteTextPay(Transaction $transaction, Record $record)
    {
        $arrayText = [
            ' - Событие : Оплачена запись № '.$record->record_id,
            ' - Филиал : '.Record::getFilial($record->company_id),
            ' - Стоимость : '.$transaction->amount. ' p.',
            ' Комментарий : '.$transaction->comment,
        ];

        return implode("\n", $arrayText);
    }

    public function createNoteLead(Record $record)
    {
        $lead = $this->amoApi->leads()->find($record->lead_id);

        $note = $lead->createNote($type = 4);

        $note->text = $this->createArrayNoteText($record);
        $note->element_type = 2;
        $note->element_id = $record->lead_id;
        $note->save();

        return $note;
    }

    private function createArrayNoteText(Record $record)
    {
        $arrayText = [
            ' - Событие : '.Record::getEvent($record->attendance),
            ' - Филиал : '.Record::getFilial($record->company_id),
            ' - Процедуры : '.$record->title,
            ' - Дата и Время : '.$record->datetime,
            ' - Мастер : '.$record->staff_name,
            ' Комментарий : '.$record->comment,
        ];

        return implode("\n", $arrayText);
    }

    public function createNoteLeadDelete(Record $record)
    {
        $lead = $this->amoApi->leads()->find($record->lead_id);

        $note = $lead->createNote($type = 4);

        $note->text = 'Запись № '.$record->order_id.' удалена из YClients';
        $note->element_type = 2;
        $note->element_id = $record->lead_id;
        $note->save();

        return $note;
    }

    public function createNoteLeadAbonementPay(Abonement $abonement)
    {
        $lead = $this->amoApi->leads()->find($abonement->lead_id);

        $note = $lead->createNote($type = 4);

        $note->text = self::createArrayNoteTextAbonementPay($abonement);
        $note->element_type = 2;
        $note->element_id = $abonement->lead_id;
        $note->save();

        return $note;
    }

    private function createArrayNoteTextAbonementPay(Abonement $abonement)
    {
        $arrayText = [
            ' - Событие : Посещение по абонементу № '.$abonement->abonement_id,
            ' - Название : '.$abonement->title,
            ' - Филиал : '.Record::getFilial($abonement->company_id),
        ];

        return implode("\n", $arrayText);
    }

    public function createNoteLeadAbonement(Abonement $abonement)
    {
        $lead = $this->amoApi->leads()->find($abonement->lead_id);

        $note = $lead->createNote($type = 4);

        $note->text = self::createArrayNoteTextAbonement($abonement);
        $note->element_type = 2;
        $note->element_id = $abonement->lead_id;
        $note->save();

        return $note;
    }

    private function createArrayNoteTextAbonement(Abonement $abonement)
    {
        $arrayText = [
            ' - Событие : Продан абонемент № '.$abonement->abonement_id,
            ' - Название : '.$abonement->title,
            ' - Филиал : '.Record::getFilial($abonement->company_id),
            ' - Стоимость : '.$abonement->sale. ' p.',
            ' - Со скидкой : '.$abonement->cost. ' p.',
            ' Комментарий : '.$abonement->comment,
        ];

        return implode("\n", $arrayText);
    }
}
