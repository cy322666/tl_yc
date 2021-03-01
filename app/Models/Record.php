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

    /*
     * 2 - Пользователь подтвердил запись,
     * 1 - Пользователь пришел, услуги оказаны,
     * 0 - Оожидание пользователя,
     * -1 - Пользователь не пришел на визит
     */
    public static function getEvent(int $attendance) :? string
    {
        switch ($attendance) {

            case -1 :
                return 'Клиент не пришел';

            case 0 :
                return 'Клиент записан';

            case 1 :
                return 'Клиент пришел';

            case 2 :
                return 'Клиент подтвердил';
        }
    }

    public static function getFilial(int $company_id) :? string
    {
        switch ($company_id) {

            case '28103'://москва
            case '1021063':

                return 'Москва';

            case '119809'://ярославль
            case '1021067':

                return 'Ярославль';

            case '119834'://рыбинск
            case '1021065':

                return 'Рыбинск';

            case '1121147'://машкова
            case '274576':

                return 'Москва Машкова';
        }
        return null;
    }

    public static function buildArrayForModel($arrayRequest)
    {
        $stringServices = '';
        $costSumm = 0;

        if(!empty($arrayRequest['data']['services'][0])) {

            foreach ($arrayRequest['data']['services'] as $array) {

                $stringServices .= $array['title'].' |';
                $costSumm += $array['cost'];
            }
            $stringServices = trim($stringServices, ' |', );
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
            'status' => 'no_pay',
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
            default:
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
            'status_id' => $status_id,
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

        return $record;
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
    //lead
    //contact
    //client
}
