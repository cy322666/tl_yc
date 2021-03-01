<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function create(Request $request)
    {
        $transaction = Transaction::getTransaction();

        $record = $transaction->record;

        if($record) {

            $this->amoApi->updateStatus($record, env('STATUS_CAME'));

            $this->amoApi->createNoteLeadTransaction($transaction, $record);

            $record->status = 'payed';
            $record->save();
        }
    }
}
