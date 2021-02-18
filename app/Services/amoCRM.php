<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Record;
use Ufee\Amo\Amoapi;

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
        //$oauth = $amoCRM->fetchAccessToken(env('AMO_AUTH_CODE'));
        $this->amoApi = \Ufee\Amo\Oauthapi::getInstance(env('AMO_CLIENT_ID'));
    }

    public function updateOrCreate(Client $client)
    {
        if($client->contact_id)
            $contact = $this->updateContact($client);
        else {
            $contact = $this->searchContact($client);

            if(!$contact) $contact = $this->createContact($client);

            $client->contact_id = $contact->id;
            $client->save();
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

    public function createLead(Record $record)
    {
        $lead = $this->amoApi->createLead();

        $lead->name = 'Новая запись в YClients';
        //$lead->responsible_user_id = $responsible;
        //$lead->status_id = $array['status'];
        $lead->save();

        return $lead;
    }

    public function updateLead(Record $record)
    {
        if($record->lead_id) {
            $lead = $this->amoApi->leads()->find($record->lead_id);//TODO lead_id

            $lead->status_id = 123;
            $lead->save();
        } else
            return null;
    }

    private function createContact(Client $client)
    {
        $contact = $this->amoApi->contacts()->create();

        $contact->cf('Email')->setValue($client->email);
        $contact->cf('Телефон')->setValue($client->phone, 'Home');
        $contact->name = $client->name;
        $contact->save();

        return $contact;
    }

    public function createTask(Record $record)
    {
        if($record->lead_id) {
            $task = $this->amoApi->createTask($type = 1);

            $task->text = 'Клиент оставил повторную заявку на сайте';
            $task->element_type = 2;
            //$task->responsible_user_id = $record->lead_id->responsible_user_id;
            $task->complete_till_at = strtotime('tomorrow');
            $task->element_id = $record->lead_id;
            $task->save();

            return $task;
        } else
            return null;
    }

    private function updateContact(Client $client)
    {
        if($client->contact_id) {
            $contact = $this->amoApi->contacts()->find($client->contact_id);

            $contact->cf('Телефон')->setValue($client->phone, 'Home');
            $contact->cf('Email')->setValue($client->email);
            $contact->save();

            return $contact;
        } else
            return null;
    }

    public function createNote(Record $record) :? Amoapi
    {
        if($record->lead_id) {
            $lead = $this->amoApi->leads()->find($record->lead_id);

            $note = $lead->createNote($type = 4);
            $note->text = $this->createArrayNoteText($record);
            $note->element_type = 2;
            $note->element_id = $record->lead_id;
            $note->save();

            return $note;
        } else
            return null;
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
