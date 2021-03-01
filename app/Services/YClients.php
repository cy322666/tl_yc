<?php

namespace App\Services;

use App\Models\Client;

class YClients
{
    public static function instance()
    {
        $yclients = new \App\Models\YClients(env('YC_PARTNER_TOKEN'));
        $yclients->getAuth(env('YC_LOGIN'), env('YC_PASSWORD'));

        return $yclients;
    }

    public static function getClient(Client $client)
    {
        $yclients = self::instance();

        return $yclients->getClient(
            $client->company_id, $client->client_id, env('YC_USER_TOKEN')
        );
    }

    public static function getAbonements(Client $client)
    {
        $yclients = self::instance();

        return $yclients->getUserAbonements(
            $client->company_id, $client->phone, env('YC_USER_TOKEN')
        );
    }
}
