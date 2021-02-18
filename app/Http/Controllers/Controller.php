<?php

namespace App\Http\Controllers;

use App\Models\YClients;
use App\Services\amoCRM;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $amoApi;
    protected $YClients;

    public function __construct()
    {
        $this->amoApi = new amoCRM();
    }
}

