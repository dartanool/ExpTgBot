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
}
