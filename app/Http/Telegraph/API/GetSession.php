<?php

namespace App\Http\Telegraph\API;

use App\Http\Services\Client\ExpeditorClient;

class GetSession
{
    private ExpeditorClient $expeditorClient;

    public function __construct()
    {
        $this->expeditorClient = new ExpeditorClient();
    }

    public function handle(string $login, string $password)
    {
        $data = [
            'Username' => $login,
            'Password' => $password
        ];

        $method ='GetSession';
        $response = $this->expeditorClient->send($method, $data);

        return $response['Pragma'];
    }

}
