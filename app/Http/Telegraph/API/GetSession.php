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

        $response = $this->expeditorClient->send($data);

        return $response['Pragma'];
    }

}
