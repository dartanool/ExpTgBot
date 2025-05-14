<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetAddressDTO;
use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\AddressKeyboard;
use App\Http\Telegraph\Keyboards\CompleteTaskKeyboard;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use DefStudio\Telegraph\Facades\Telegraph;

class CompleteTask
{
    private int $userId;
    private ExpeditorApiService $expeditorApiService;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->expeditorApiService = new ExpeditorApiService($userId);
    }

        public function handle()
    {
        $response = $this->expeditorApiService->getTaskList($this->userId);
        Telegraph::message('Вот ваш список')->keyboard(CompleteTaskKeyboard::handle($response->trips))->send();

    }

    public function selectTripTask(string $tripId)
    {
        $userId = $this->userId;
        $response = $this->expeditorApiService->getTaskList($userId);

        // Получаем данные задания (может быть из кэша или нового API-запроса)
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);


        Telegraph::message($this->formatTripDetails($trip))->keyboard(CompleteTaskKeyboard::createDetailsKeyboard($trip))
            ->send();
        // Отправляем сообщение с новой клавиатурой

    }

    public function getAddressList(string $tripId)
    {
        $userId = $this->userId;

        $response = $this->expeditorApiService->getAddressList($tripId);

        Telegraph::message('Список адресов')->keyboard(AddressKeyboard::show($response->addresses, $tripId))->send();

    }


    public function selectAddress(string $addressId, string $tripId)
    {
        $userId = $this->userId;
        $addresses = $this->expeditorApiService->getAddressList($tripId);

        $address = $this->expeditorApiService->getAddressById($addressId, $addresses->addresses);

        Telegraph::message($this->sendAddressCard($address))->send();


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

    protected function sendAddressCard(GetAddressDTO $address)
    {
       return $message = "🏢 *Клиент:* {$address->clientName}\n"
            . "📌 *Адрес:* {$address->address}\n"
            . "🕒 *Часы работы:* {$address->workHours}\n"
            . "📍 [Карта](https://yandex.ru/maps/?ll={$address->lon},{$address->lat})";

    }
}

