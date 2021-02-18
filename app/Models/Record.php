<?php

namespace App\Models;

use App\Services\YClients;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    public static function getRecord()
    {
        $arrayForRecord = self::buildArrayForModel(Request::capture()->toArray());

        $record = Record::updateOrCreate($arrayForRecord);

        return $record;
    }

    public function client()
    {
        return $this->belongsTo('App\Client');
    }
    //lead
    //contact
    //client
}
