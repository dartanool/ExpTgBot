<?php

namespace App\Http\Telegraph\API;

use App\DTO\GetTasksListDTO;
use App\Http\Services\TelegraphService;
use App\Models\Telegraph\TelegraphUsers;

class GetTaskListAPI extends BaseAPI
{
    public function handle(int $userId) : GetTasksListDTO
    {
        $token = TelegraphUsers::query()->where('user_id', $userId)->first()->token;
        $method = 'rt';

        $data = [
            'Pragma' => "$token ",
            'init' => [
                'type' => 'data',
                'report' => 'te.trips.r'
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);

        return $this->apiService->parseApiResponse($response);
    }
}
