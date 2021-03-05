<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Abonement extends Model
{
    protected $primaryKey = 'abonement_id';
    protected $fillable = [
        'abonement_id',
        'company_id',
        'title',
        'cost',
        'client_id',
        'is_active',
        'lead_id',
        'comment',
        'cost',//без бонусов
        'sale',//бюджет
        'balance'//остаток на балансе
    ];

    public static function getAbonement()
    {
        $arrayForAbonement = self::buildArrayForModel(Request::capture()->toArray());

        $abonement = self::create($arrayForAbonement);

        return $abonement;
    }

    public static function buildArrayForModel($arrayRequest)
    {
        $arrayForModel = [
            'record_id'  => $arrayRequest['data']['record_id'],
            'company_id' => $arrayRequest['company_id'],
            'abonement_id' => $arrayRequest['data']['id'],
            'title' => $arrayRequest['data']['good']['title'],
            'client_id' => $arrayRequest['data']['client']['id'],
            'comment' => $arrayRequest['data']['comment'],
            'cost' => $arrayRequest['data']['cost'],
            'balance' => $arrayRequest['data']['cost'],
            'is_active' => 1,
            'sale' => self::getSaleByTitle($arrayRequest['data']['good']['title']),
        ];

        return $arrayForModel;
    }

    public static function checkName(string $title) : bool
    {
        if (strripos($title, 'ДК_') !== false ||
            strripos($title, 'С_') !== false) {

            return true;

        } else

            return false;
    }

    public static function getSaleByTitle(string $title) : int
    {
        $str_at = explode('(', $title);
        $str_to = explode(')', $str_at[1]);

        return trim($str_to[0]);
    }

    public static function getCostByTitle(string $title)
    {
        $str_at = explode('  ', $title);
        $str_to = explode('руб.', end($str_at));

        return trim($str_to[0]);
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
