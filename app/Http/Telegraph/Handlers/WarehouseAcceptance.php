<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use DefStudio\Telegraph\Facades\Telegraph;

class WarehouseAcceptance
{
    private ExpeditorApiService $expeditorApiService;

    public function __construct(int $userId)
    {
        $this->expeditorApiService = new ExpeditorApiService($userId);
    }
    public function handle(string $tripId)
    {
        $this->expeditorApiService->acceptanceFromWarehouse($tripId);

        $response = $this->expeditorApiService->getTaskList();
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);

        Telegraph::message($this->formatTripDetails($trip))->keyboard(TaskListKeyboard::createDetailsKeyboard($trip))
            ->send();
    }

    /**
     * @throws \Exception
     */

    public function completeAcceptation(string $tripId)
    {

        Telegraph::message('Complete acceptation')->send();
//        $response = $this->expeditorApiService->completeAcceptation($tripId);
    }

    public function cancelEvent(string $tripId)
    {
        Telegraph::message('Cancel event')->send();
//        $response = $this->expeditorApiService->cancelEvent($tripId);
    }


    public function finishAcceptation(string $tripId)
    {
        Telegraph::message('Finish acceptation')->send();
//        $response = $this->expeditorApiService->finishAcceptation($tripId);
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
