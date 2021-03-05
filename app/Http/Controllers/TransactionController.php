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

            if($record->lead_id) {

                $lead = $this->amoApi->getLead($record->lead_id);

                $this->amoApi->updateStatus($lead, intval($this->amoApi::pipelineHelper($lead->pipeline_id, $record)));

                $this->amoApi->createNoteLeadTransaction($transaction, $record);

                $record->status = 'payed';
                $record->save();
            }
        }
    }
}
