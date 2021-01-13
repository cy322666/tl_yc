<?php

namespace App\Http\Controllers;

use App\Services\ServiceAmoCRM;
use App\Services\ServiceYClients;
use Illuminate\Http\Request;
use App\Client;

class ClientController extends Controller
{
    public $YClients;
    public $amoCRM;

    public function __construct()
    {
        $this->YClients = new ServiceYClients();
        $this->amoCRM   = new ServiceAmoCRM();
    }

    public function createClient()
    {
    }
}
