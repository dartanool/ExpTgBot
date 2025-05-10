<?php

namespace App\Http\Telegraph\API;

use App\Http\Services\Client\ExpeditorClient;

class GetSessionAPI extends BaseAPI
{


    public function handle(string $login, string $password)
    {
        $data = [
            'Username' => $login,
            'Password' => $password
        ];

        $method ='GetSession';
        $response = $this->expeditorClient->auth($method, $data);

        return $response['Pragma'];
    }

}
