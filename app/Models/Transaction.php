<?php

namespace App;

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
    /*
 * {
"company_id": 28103,
"resource": "finances_operation",
"resource_id": 199254095,
"status": "create",
"data": {
"id": 199254095,
"document_id": 258345769,
"expense": {
  "id": 8,
  "title": "Прочие доходы"
},
"date": "2020-09-21T12:30:00+0400",
"amount": 100,
"comment": "",
"master": [],
"supplier": [],
"account": {
  "id": 17433,
  "title": "Основная касса"
},
"client": {
  "id": 73168632,
  "name": "ГЛ Покровка - Машкова 7999637",
  "phone": "+79996373955"
},
"record_id": 224086399,
"visit_id": 189519462,
"sold_item_id": 0,
"sold_item_type": ""
}
}
 */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
