<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\YClients;

class Client extends Model
{
    protected $guarded  = [];
    protected $fillable = [
        'client_id',
        'name',
        'phone',
        'email',
        'birth_date',
        'spent',
        'company_id',
        'success_visits_count',
        'spent',
        'contact_id',
    ];

    public static function buildArrayForModel($arrayRequest)
    {
        $arrayForModel = [
            'company_id' => $arrayRequest['company_id'],
            'client_id' => $arrayRequest['data']['client']['id'],
            'name' => $arrayRequest['data']['client']['name'],
            'phone' => $arrayRequest['data']['client']['phone'],
            'email' => $arrayRequest['data']['client']['email'],
            'success_visits_count' => $arrayRequest['data']['client']['success_visits_count'],
        ];

        return $arrayForModel;
    }

    public static function getClient()
    {
        $arrayForClient = self::buildArrayForModel(Request::capture()->toArray());

        $client = Client::updateOrCreate($arrayForClient);

        $yclient = YClients::getClient($client);

        $client->fill($yclient);
        $client->save();

        return $client;
    }

    public function getRouteKeyName()
    {
        return 'client_id';
    }

    public function records()
    {
        return $this->hasMany('App\Record');
    }
}
