<?php

namespace App\Http\Telegraph\API\Location;

use App\Http\Telegraph\API\BaseAPI;
use App\Models\Telegraph\TelegraphUsers;
use App\Models\Telegraph\TelegraphUserState;

class GetStationIdAPI extends BaseApi
{

    public function handle(int $userId, string $station, string $cityId)
    {
        $token = TelegraphUsers::query()->where('user_id', $userId)->first()->token;
        $method = 'rt';


        $data = [
            'Pragma' => "$token",
            'init' => [
                'type' => 'data',
                'report' => 'te.mst.r'
            ],
            'params' => [
                'idKg' => $cityId,
                'mstName' => "{$station}%"
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);

        return $this->apiService->getStationId($response);
    }

}
