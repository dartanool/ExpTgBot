<?php

namespace App\Http\Services;

use App\DTO\GetTaskDTO;
use App\DTO\GetTasksListDTO;
use App\Http\Telegraph\API\GetTaskListAPI;
use Illuminate\Support\Collection;

class ApiService
{

    public function getCityId(array $response): int
    {
        return (int) ($response['result'][0]['ID_KG'] ?? 0);
    }

    public function getStationId(array $response): int
    {
        return (int) ($response['result'][0]['ID_MST'] ?? 0);
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
}
