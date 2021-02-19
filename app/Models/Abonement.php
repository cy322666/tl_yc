<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Abonement extends Model
{
    protected $fillable = [
        'abonement_id',
        'company_id',
        'title',
        'cost',
        'client_id',
        'comment',
        'lead_id',
        'cost',//без бонусов
        'sale',//бюджет
    ];

    public static function getAbonement()
    {
        $arrayForClient = self::buildArrayForModel(Request::capture()->toArray());

        $client = Abonement::create($arrayForClient);

        $yclient = YClients::getClient($client);

        $client->fill($yclient);
        $client->save();

        return $client;
    }

    public static function buildArrayForModel($arrayRequest)
    {
        $arrayForModel = [
            'record_id'  => $arrayRequest['resource_id'],
            'company_id' => $arrayRequest['company_id'],
            'title' => $arrayRequest['data']['good']['title'],
            'client_id' => $arrayRequest['data']['client']['id'],
            'comment' => $arrayRequest['data']['comment'],
            'cost' => $arrayRequest['data']['cost'],
            'sale' => self::getSaleByCost($arrayRequest['data']['cost']),
        ];

        return $arrayForModel;
    }

    public static function getSaleByCost(int $cost) : int
    {
        $str_at = explode('(', $cost);
        $str_to = explode(')', $str_at[1]);

        return trim($str_to);
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
