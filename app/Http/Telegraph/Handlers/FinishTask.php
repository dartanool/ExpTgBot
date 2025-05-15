<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\CompleteTaskKeyboard;
use App\Http\Telegraph\Keyboards\FinishTaskKeyboard;
use DefStudio\Telegraph\Facades\Telegraph;

class FinishTask
{
    private ExpeditorApiService $expeditorApiService;

    public function __construct(int $userId)
    {
        $this->expeditorApiService = new ExpeditorApiService($userId);
    }
    public function handle(string $tripId)
    {
        $response = $this->expeditorApiService->getTaskList();
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);

        Telegraph::message($this->formatTripDetails($trip))->keyboard(FinishTaskKeyboard::handle($trip->id))
            ->send();
    }

    public function arrivedToUnload(string $tripId)
    {
        $this->expeditorApiService->arrivedToUnload($tripId);
    }
    public function completeDelivery(string $tripId)
    {
        $this->expeditorApiService->completeDelivery($tripId);
    }
    public function submitVehicleAndDocuments(string $tripId)
    {
        $this->expeditorApiService->submitVehicleAndDocuments($tripId);
    }


    private function formatTripDetails(GetTaskDTO $trip): string
    {
        return <<<TEXT
        🚛 *Детали задания #{$trip->id}*

        *Машина:* {$trip->carNumber}
        *Город:* {$trip->cityName}
        *Время:* {$trip->startDate} - {$trip->endDate}

        *Статистика:*
        - Всего поручений: {$trip->totalTasks}
        - Доставка: {$trip->deliveryTasksCount} (Вес: {$trip->deliveryWeight} кг)
        - Забор: {$trip->pickupTasksCount} (Вес: {$trip->pickupWeight} кг)

        *Статус:* {$this->getStatusText($trip)}
        TEXT;
    }

    private function getStatusText(GetTaskDTO $trip): string
    {
        return match($trip->statusReady) {
            1 => 'Готов к выполнению',
            2 => 'Завершено',
            default => 'Запланировано',
        };
    }
}
