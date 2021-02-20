<?php

namespace App\Services;

use App\Models\Abonement;
use App\Models\Client;
use App\Models\Record;
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

    public function updateOrCreate(Client $client)
    {
        if($client->contact_id)

            $contact = $this->updateContact($client);
        else {
            $contact = $this->searchContact($client);

            if(!$contact) $contact = $this->createContact($client);
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

        $client->contact_id = $contact->id;
        $client->save();

        return $contact;
    }

    public function createLead(Client $client, Record $record)
    {
        $lead = $this->amoApi->leads()->create();

        $lead->name = 'Запись в YClients';
        //$lead->responsible_user_id = $responsible;
        //TODO кастомные поля
        $lead->contacts_id = $client->contact_id;
        $lead->status_id = Record::getStatus($record->attendance)['id'];
        $lead->save();

        $record->lead_id = $lead->id;
        $record->save();

        return $lead;
    }

    public function createAbonement(Client $client, Abonement $abonement)
    {
        $lead = $this->amoApi->leads()->create();

        $lead->name = 'Абонемент в YClients';
        //$lead->responsible_user_id = $responsible;
        //TODO кастомные поля
        //TODO статус для продажи
        $lead->contacts_id = $client->contact_id;
        $lead->status_id = env('STATUS_ABONEMENT');
        $lead->save();

        $abonement->lead_id;
        $abonement->save();

        return $abonement;
    }

    public function updateLead(Record $record)
    {
        if($record->lead_id) {

            $lead = $this->amoApi->leads()->find($record->lead_id);

            //TODO body?

            $lead->save();

        } else
            return null;
    }

    public function updateStatus(Record $record, int $status_id)
    {
        $lead = $this->amoApi->leads()->find($record->lead_id);

        $lead->status_id = $status_id;
        $lead->save();
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

            if(!$lead)
                $this->createLead($client, $record);
        }

        $record->lead_id;
        $record->save();

        return $lead;
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
            foreach ($leads as $lead) {

                if ($lead->pipeline_id == $pipeline_id) return $lead;
            }
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

    public function updateCustomFields(Record $record)
    {

    }

    //TODO текст по евенту
    public function createNoteLead(Record $record, string $event)
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
            'Запись в YClients',
            ' - {событие}',
            ' - {филиал}',
            ' - {процедуры}',
            ' - {дата и время}',
            ' - {мастер}',
            ' {комментарий}',
        ];

        return implode("\n", $arrayText);
    }
}
