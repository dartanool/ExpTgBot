<?php

namespace App\Http\Telegraph\API\Location;

use App\Http\Telegraph\API\BaseAPI;
use App\Models\Telegraph\TelegraphUsers;

class GetCityIdAPI extends BaseAPI
{
    public function handle(int $userId, string $city)
    {
        $token = TelegraphUsers::query()->where('user_id', $userId)->first()->token;
        $method = 'rt';

        $data = [
            'Pragma' => "$token",
            'init' => [
                'type' => 'data',
                'report' => 'te.kg.r'
            ],
            'params' => [
                'KgName' => "{$city}%"
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);

        return $response['result'];
    }

}
