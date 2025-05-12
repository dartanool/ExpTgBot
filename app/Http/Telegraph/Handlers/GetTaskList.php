<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use DefStudio\Telegraph\DTO\Message;
use \DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;

class GetTaskList
{
    private int $userId;
    private ExpeditorApiService $expeditorApiService;

    public function __construct(int $chatId)
    {
        $this->expeditorApiService = new ExpeditorApiService($chatId);

        $this->userId = $chatId;
    }
    public function handle()
    {

        $response = $this->expeditorApiService->getTaskList($this->userId);
        Telegraph::message('Вот ваш список')->keyboard(TaskListKeyboard::handle($response->trips)) ->send();

    }

    /**
     * @throws \Exception
     */
    public function selectTrip( string $tripId): void
    {
//
        $userId = $this->userId;
        $response = $this->expeditorApiService->getTaskList($userId);


        // Получаем данные задания (может быть из кэша или нового API-запроса)
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);


        Telegraph::message($this->formatTripDetails($trip))->keyboard(TaskListKeyboard::createDetailsKeyboard($trip))
            ->send();
        // Отправляем сообщение с новой клавиатурой

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
