<?php

namespace App\Http\Services;

use App\DTO\GetTaskDTO;
use App\DTO\GetTasksListDTO;
use App\Http\Services\Client\ExpeditorClient;
use App\Models\Telegraph\TelegraphUsers;
use Illuminate\Support\Collection;

class ExpeditorApiService
{

    protected ExpeditorClient $expeditorClient;
    private string $token;

    public function __construct(int $userId)
    {
        $this->expeditorClient = new ExpeditorClient();

        $this->token = TelegraphUsers::query()->where('user_id', $userId)->first()->token;
    }




    public function parseApiResponse(array $apiResponse): GetTasksListDTO
    {
        $trips = [];

        foreach ($apiResponse['result'] as $taskData) {
            $trips[] = new GetTaskDTO(
                id: $taskData['ID_AEX_TRIP'],
                trsId: $taskData['AEX_TRIP_ID_TRS'],
                carNumber: $taskData['TRS_SID'],
                startDate: $taskData['AEX_TRIP_DT_BG'],
                endDate: $taskData['AEX_TRIP_DT_END'],
                cityId: $taskData['ID_KG'],
                cityName: $taskData['KG_NAME'],
                totalTasks: (int)$taskData['Z_CNT'],
                deliveryTasksCount: (int)$taskData['Z_DST_CNT'],
                pickupTasksCount: (int)$taskData['Z_ZBR_CNT'],
                deliveryWeight: (float)str_replace(',', '.', $taskData['Z_DST_VES']),
                pickupWeight: (float)str_replace(',', '.', $taskData['Z_ZBR_VES']),
                deliveryVolume: (float)str_replace(',', '.', $taskData['Z_DST_OBYOM']),
                pickupVolume: (float)str_replace(',', '.', $taskData['Z_ZBR_OBYOM']),
                statusReady: (int)$taskData['S71'],
                statusCompleted: (int)$taskData['S48'],
            );
        }

        return new GetTasksListDTO(
            success: $apiResponse['result'] === '1',
            trips: $trips,
        );
    }


    public function getTripById(string $tripId, array $trips): GetTaskDTO
    {
        foreach ($trips as $trip) {
            if ($trip->id === $tripId) {
                return $trip;
            }
        }

        throw new \Exception("Задание не найдено");
    }


    public function getSession(string $login, string $password)
    {
        $data = [
            'Username' => $login,
            'Password' => $password
        ];

        $method ='GetSession';
        $response = $this->expeditorClient->auth($method, $data);

        return $response['Pragma'];
    }

    public function getCityId(int $userId, string $city)
    {
        $method = 'rt';

        $data = [
            'Pragma' => "$this->token",
            'init' => [
                'type' => 'data',
                'report' => 'te.kg.r'
            ],
            'params' => [
                'KgName' => "{$city}%"
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);

        return (int) ($response['result'][0]['ID_KG'] ?? 0);

    }

    public function getStationId(int $userId, string $station, string $cityId)
    {
        $method = 'rt';


        $data = [
            'Pragma' => "$this->token",
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

        return (int) ($response['result'][0]['ID_MST'] ?? 0);

    }

    public function setUserStation(int $userId, int $stationId)
    {
        $method = 'SetUserMst';

        $data = [
            'Pragma' => "$this->token",
            'mst' => "$stationId"
        ];

        $this->expeditorClient->send($method, $data);
    }

    public function getTaskList(int $userId) : GetTasksListDTO
    {
        $method = 'rt';

        $data = [
            'Pragma' => "$this->token ",
            'init' => [
                'type' => 'data',
                'report' => 'te.trips.r'
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);

        return $this->parseApiResponse($response);
    }

}
