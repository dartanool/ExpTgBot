<?php

namespace App\Http\Telegraph\API;

use App\Http\Services\Client\ExpeditorClient;

class SetUserStation
{
    private ExpeditorClient $expeditorClient;

    public function __construct()
    {
        $this->expeditorClient = new ExpeditorClient();
    }
    public function handle()
    {

    }
}
