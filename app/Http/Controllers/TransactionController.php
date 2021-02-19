<?php

namespace App\Http\Controllers;

use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function create(Request $request)
    {
        $transaction = Transaction::getTransaction();

        $record = $transaction->record;

        $this->amoApi->updateStatus($record, 1);

        $this->amoApi->createNoteLead($record, 'came');
    }
}
