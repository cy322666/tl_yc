<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Abonement extends Model
{
    protected $fillable = [
        'abonement_id',
        'company_id',
        //'name',
        'cost',
        'client_id',
        //'type_id',
        'comment',
        //'record_id',
        'lead_id'
    ];

    /*
     * {
  "company_id": 28103,
  "resource": "goods_operations_sale",
  "resource_id": 176687032,
  "status": "create",
  "data": {
    "id": 176687032,
    "document_id": 260335064,
    "type_id": 1,
    "type": "Продажа товара",
    "good": {
      "id": 12911618,
      "title": "ДК_Спа услуги GOLD    30000 руб. (34500) Тульская"
    },
    "storage": {
      "id": 18758,
      "title": "Товары"
    },
    "unit": {
      "id": 216760,
      "title": "Штука",
      "short_title": "шт."
    },
    "operation_unit_type": 1,
    "amount": -1,
    "create_date": "2020-09-28T17:33:32+0400",
    "cost_per_unit": 30000,
    "cost": 30000,
    "discount": 0,
    "comment": "",
    "master": {
      "id": 216061,
      "title": "Администратор"
    },
    "supplier": [],
    "record_id": 0,
    "service": [],
    "client": {
      "id": 73168632,
      "name": "ТЕСТ РАЗРАБОТКА",
      "phone": "79996373955"
    },
    "last_change_date": "2020-09-28T18:05:30+0400"
  }
}
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }
}
