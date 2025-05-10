<?php

namespace App\Http\Telegraph\API\Location;

use App\Http\Telegraph\API\BaseAPI;
use App\Models\Telegraph\TelegraphUsers;

class SetUserStationAPI extends BaseAPI
{
    public function handle(int $userId, int $stationId)
    {
        $token = TelegraphUsers::query()->where('user_id', $userId)->first()->token;
        $method = 'SetUserMst';

        $data = [
            'Pragma' => "$token",
            'mst' => "$stationId"
        ];

        $this->expeditorClient->send($method, $data);
    }

}
