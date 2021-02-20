<?php

namespace App\Models;

use App\Services\YClients;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Record extends Model
{
    protected $primaryKey = 'record_id';
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
            'status' => self::getStatus($arrayRequest['data']['attendance'])['name'],
        ];

        return $arrayForModel;
    }

    public static function getStatus(int $attendance): array
    {
        switch ($attendance) {//TODO актуализировать статусы
            /*
             * 3 - Запись удалена,
             * 2 - Пользователь подтвердил запись,
             * 1 - Пользователь пришел, услуги оказаны,
             * 0 - Оожидание пользователя,
             * -1 - Пользователь не пришел на визит
             */
            case -1 :
                $status_name = 'did_not_come';
                $action = 'cancel';
                $status_id = env('STATUS_CANCEL');
                break;

            case 0 :
                $status_name = 'waiting';
                $action = 'wait';
                $status_id = env('STATUS_WAIT');
                break;

            case 1 :
                $status_name = 'came';
                $action = 'came';
                $status_id = env('STATUS_CAME');
                break;

            case 2 :
                $status_name = 'confirmed';
                $action = 'confirm';
                $status_id = env('STATUS_CONFIRM');
                break;

            case 3 :
                $status_name = 'delete';
                $action = 'delete';
                $status_id = env('STATUS_DELETE');
                break;
        }

        return [
            'id' => $status_id,
            'name' => $status_name,
            'action' => $action,
        ];
    }

    public static function getRecord()
    {
        $arrayForRecord = self::buildArrayForModel(Request::capture()->toArray());

        $record = Record::find($arrayForRecord['record_id']);

        if(!$record)

            $record = Record::create($arrayForRecord);
        else
            $record->fill($arrayForRecord);

        $record->status = Record::getStatus($arrayForRecord['attendance'])['id'];
        $record->save();

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
