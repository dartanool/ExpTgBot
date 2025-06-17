<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\WarehouseActionKeyboard;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use App\Models\Telegraph\TelegraphUserLocation;
use DefStudio\Telegraph\Models\TelegraphChat;

class GetTaskList
{
    private ExpeditorApiService $expeditorApiService;
    private TelegraphChat $chat;

    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
        $this->expeditorApiService = new ExpeditorApiService($chat->chat_id);
    }
    public function handle()
    {
        $data = TelegraphUserLocation::query()->where('user_id',$this->chat->chat_id)->first();

        if ($data->event_lon && $data->event_lat) {
            $response = $this->expeditorApiService->getTaskList();
            $this->chat->message('Список текущих и плановых заданий:')->keyboard(TaskListKeyboard::handle($response->trips))->send();
        } else {
            $this->chat->message("Передайте данные о местоположении.\nНажмите: Определить местоположение")->send();
        }
    }
    /**
     * @throws \Exception
     */
    public function selectTrip(int $messageId, string $tripId): void
    {
        $this->chat->deleteMessage($messageId)->send();

        $response = $this->expeditorApiService->getTaskList();
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);

        $location = TelegraphUserLocation::query()->where('user_id', $this->chat->chat_id)->first();
        $this->expeditorApiService->markAsRead($tripId, $location->event_lat, $location->event_lon);

        $this->chat->message($this->formatTripDetails($trip))->keyboard(WarehouseActionKeyboard::handle($tripId))->send();

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
