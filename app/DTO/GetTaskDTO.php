<?php

namespace App\DTO;

class GetTaskDTO
{
    public function __construct(
        public string $id,
        public string $trsId,
        public string $carNumber,
        public string $startDate,
        public string $endDate,
        public string $cityId,
        public string $cityName,
        public int $totalTasks,
        public int $deliveryTasksCount,
        public int $pickupTasksCount,
        public float $deliveryWeight,
        public float $pickupWeight,
        public float $deliveryVolume,
        public float $pickupVolume,
        public int $statusReady,  // S71
        public int $statusCompleted,  // S48
    ) {}

//    public function parseApiResponse(array $apiResponse): GetTasksListDTO
//    {
//        $trips = [];
//
//        foreach ($apiResponse['result'] as $taskData) {
//            $trips[] = new GetTaskDTO(
//                id: $taskData['ID_AEX_TRIP'],
//                trsId: $taskData['AEX_TRIP_ID_TRS'],
//                carNumber: $taskData['TRS_SID'],
//                startDate: $taskData['AEX_TRIP_DT_BG'],
//                endDate: $taskData['AEX_TRIP_DT_END'],
//                cityId: $taskData['ID_KG'],
//                cityName: $taskData['KG_NAME'],
//                totalTasks: (int)$taskData['Z_CNT'],
//                deliveryTasksCount: (int)$taskData['Z_DST_CNT'],
//                pickupTasksCount: (int)$taskData['Z_ZBR_CNT'],
//                deliveryWeight: (float)str_replace(',', '.', $taskData['Z_DST_VES']),
//                pickupWeight: (float)str_replace(',', '.', $taskData['Z_ZBR_VES']),
//                deliveryVolume: (float)str_replace(',', '.', $taskData['Z_DST_OBYOM']),
//                pickupVolume: (float)str_replace(',', '.', $taskData['Z_ZBR_OBYOM']),
//                statusReady: (int)$taskData['S71'],
//                statusCompleted: (int)$taskData['S48'],
//            );
//        }
//
//        return new GetTasksListDTO(
//            success: $apiResponse['result'] === '1',
//            trips: $trips,
//        );
//    }
}
