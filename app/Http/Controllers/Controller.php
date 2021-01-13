<?php

namespace App\Http\Controllers;

use App\Models\YClients;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $amoCRM;
    protected $YClients;

    public function __construct()
    {
        $this->YClients = new YClients(env('YC_PARTNER_TOKEN'));
        $this->YClients->getAuth(env('YC_LOGIN'), env('YC_PASSWORD'));
    }
}

