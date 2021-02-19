<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'company_id',
        'amount',
        'client_id',
        'visit_id',
        'record_id',
        'comment',
    ];

    public static function getTransaction()
    {
        $arrayForRecord = self::buildArrayForModel(Request::capture()->toArray());

        $transaction = Transaction::create($arrayForRecord);

        return $transaction;
    }

    public static function buildArrayForModel($arrayRequest)
    {
        $arrayForModel = [
            'record_id'  => $arrayRequest['resource_id'],
            'company_id' => $arrayRequest['company_id'],
            'client_id' => $arrayRequest['data']['client']['id'],
            'visit_id' => $arrayRequest['data']['visit_id'],
            'amount' => $arrayRequest['data']['amount'],
            'comment' => $arrayRequest['data']['comment'],
        ];

        return $arrayForModel;
    }

    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    public function record()
    {
        return $this->belongsTo('App\Record');
    }
}
