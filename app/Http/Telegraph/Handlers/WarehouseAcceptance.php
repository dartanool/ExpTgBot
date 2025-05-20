<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use App\Models\Telegraph\TelegraphUserLocation;
use DefStudio\Telegraph\Facades\Telegraph;

class WarehouseAcceptance
{
    private ExpeditorApiService $expeditorApiService;
    private int $userId;
    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->expeditorApiService = new ExpeditorApiService($userId);
    }
    public function handle(int $messageId, string $tripId)
    {
        $this->expeditorApiService->acceptanceFromWarehouse($tripId);

        $response = $this->expeditorApiService->getTaskList();
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);
        Telegraph::deleteMessage($messageId)->send();

        Telegraph::message($this->formatTripDetails($trip))->keyboard(TaskListKeyboard::createDetailsKeyboard($trip))
            ->send();
    }

   public function markAsRead(string $tripId)
   {
        $location = TelegraphUserLocation::query()->where('user_id', $this->userId)->first();
        $this->expeditorApiService->markAsRead($tripId, $location->event_lat, $location->event_lon);
   }
    public function moveByOrder(string $tripId)
    {
        $location = TelegraphUserLocation::query()->where('user_id', $this->userId)->first();
        $this->expeditorApiService->moveByOrder($tripId, $location->event_lat, $location->event_lon);
    }

    public function completeAcceptation(string $tripId)
    {
        Telegraph::message('Для создания события необходимо выполнить команду 📍 Отправить местоположение')->send();

        $location = TelegraphUserLocation::query()->where('user_id', $this->userId)->first();

        if ($location->event_lat & $location->event_lon) {
            $response = $this->expeditorApiService->completeAcceptation($tripId);
        } else {
            Telegraph::message('Необходимо выполнить команду 📍 Отправить местоположение')->send();
        }
    }


    public function cancelEvent(string $tripId)
    {
        Telegraph::message('Cancel event')->send();
        $response = $this->expeditorApiService->cancelEvent($tripId);
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
