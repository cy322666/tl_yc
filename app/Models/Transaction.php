<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $primaryKey = 'transaction_id';
    protected $fillable = [
        'company_id',
        'amount',
        'client_id',
        'visit_id',
        'record_id',
        'transaction_id',
        'comment',
    ];

    public static function getTransaction()
    {
        $request = Request::capture()->toArray();

        if($request['data']['record_id'] != 0) {

            $arrayForRecord = self::buildArrayForModel($request);

            $transaction = Transaction::create($arrayForRecord);

            $record = $transaction->record;

            $record->attendance = 1;
            $record->save();
        }

        return $transaction;
    }

    public static function buildArrayForModel($arrayRequest)
    {
        $arrayForModel = [
            'record_id'  => $arrayRequest['data']['record_id'],
            'transaction_id'  => $arrayRequest['data']['id'],
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
        return $this->belongsTo('App\Models\Client');
    }

    public function record()
    {
        return $this->belongsTo('App\Models\Record', 'record_id', 'record_id');
    }
}
