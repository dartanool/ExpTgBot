<?php

namespace App\Http\Telegraph\API;

use App\Http\Services\ExpeditorApiService;
use App\Http\Services\Client\ExpeditorClient;

class BaseAPI
{
    protected ExpeditorClient $expeditorClient;
    protected ExpeditorApiService $apiService;
//    private string $token;

    public function __construct()
    {
        $this->expeditorClient = new ExpeditorClient();
        $this->apiService = new ExpeditorApiService();
//        $this
    }
}
