<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Record extends Model
{
    protected $guarded  = [];
    protected $fillable = [
        'record_id',
        'company_id',
        'title',
        'cost',
        'staff_id',
        'client_id',
        'visit_id',
        'datetime',
        'comment',
        'seance_length',
        'attendance',
        'status',
        'lead_id',
    ];

    public static function buildArrayForModel($arrayRequest)
    {
        $stringServices = '';
        $costSumm = 0;

        if(!empty($arrayRequest['data']['services'][0])) {

            foreach ($arrayRequest['data']['services'] as $array) {

                $stringServices .= $array['title'].' |';
                $costSumm += $array['cost'];
            }
            //$stringServices = trim(' |', $stringServices);
        }

        $arrayForModel = [
            'record_id'  => $arrayRequest['resource_id'],
            'company_id' => $arrayRequest['company_id'],
            'title' => $stringServices,
            'cost' => $costSumm,
            'staff_id' => $arrayRequest['data']['staff_id'],
            'client_id' => $arrayRequest['data']['client']['id'],
            'visit_id' => $arrayRequest['data']['visit_id'],
            'datetime' => Carbon::parse($arrayRequest['data']['datetime'])->format('Y.m.d H:i:s'),
            'comment' => $arrayRequest['data']['comment'],
            'seance_length' => $arrayRequest['data']['length'],
            'attendance' => $arrayRequest['data']['attendance'],
            'status' => Record::switchStatus($arrayRequest['data']['attendance']),
        ];

        return $arrayForModel;
    }

    public static function switchStatus($attendance)
    {
        /*
         * 2 - Пользователь подтвердил запись,
         * 1 - Пользователь пришел, услуги оказаны,
         * 0 - ожидание пользователя,
         * -1 - пользователь не пришел на визит
         */
        switch ($attendance) {
            case '-1' :
                $logicAction = 'did_not_come';
                break;
            case '0' :
                $logicAction = 'waiting';
                break;
            case '1' :
                $logicAction = 'came';
                break;
            case '2' :
                $logicAction = 'confirmed';
                break;
        }
        return $logicAction;
    }

    public function client()
    {
        return $this->belongsTo('App\Client');
    }
    //lead
    //contact
    //client

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
