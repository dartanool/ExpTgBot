<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\CompleteTaskKeyboard;
use App\Http\Telegraph\Keyboards\FinishTaskKeyboard;
use App\Models\Telegraph\TelegraphUserLocation;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;

class FinishTask
{
    private ExpeditorApiService $expeditorApiService;
    private TelegraphChat $chat;
    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
        $this->expeditorApiService = new ExpeditorApiService($chat->chat_id);
    }
    public function handle(int $messageId,string $tripId)
    {
        $this->chat->deleteMessage($messageId)->send();

        $response = $this->expeditorApiService->getTaskList();
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);

        $this->chat->message($this->formatTripDetails($trip))->keyboard(FinishTaskKeyboard::handle($trip->id))->send();
    }

    public function arrivedToUnload(string $tripId)
    {
        $this->expeditorApiService->arrivedToUnload($tripId, $this->getLocation()->event_lat, $this->getLocation()->event_lon);
    }
    public function completeDelivery(string $tripId)
    {
        $this->expeditorApiService->completeDelivery($tripId, $this->getLocation()->event_lat, $this->getLocation()->event_lon);
    }
    public function submitVehicleAndDocuments(string $tripId)
    {
        $this->expeditorApiService->submitVehicleAndDocuments($tripId, $this->getLocation()->event_lat, $this->getLocation()->event_lon);
    }

    private function getLocation()
    {
        return TelegraphUserLocation::query()->where('user_id', $this->chat->chat_id)->first();
    }

    private function formatTripDetails(GetTaskDTO $trip): string
    {
        return <<<TEXT
        🚛 Детали задания #{$trip->id}

        Машина: {$trip->carNumber}
        Город: {$trip->cityName}
        Время: {$trip->startDate} - {$trip->endDate}

        Статистика:
        - Всего поручений: {$trip->totalTasks}
        - Доставка: {$trip->deliveryTasksCount} (Вес: {$trip->deliveryWeight} кг)
        - Забор: {$trip->pickupTasksCount} (Вес: {$trip->pickupWeight} кг)

        Статус: {$this->getStatusText($trip)}
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
