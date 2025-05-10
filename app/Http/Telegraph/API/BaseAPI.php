<?php

namespace App\Http\Telegraph\API;

use App\Http\Services\ApiService;
use App\Http\Services\Client\ExpeditorClient;

class BaseAPI
{
    protected ExpeditorClient $expeditorClient;
    protected ApiService $apiService;
//    private string $token;

    public function __construct()
    {
        $this->expeditorClient = new ExpeditorClient();
        $this->apiService = new ApiService();
//        $this
    }
}
