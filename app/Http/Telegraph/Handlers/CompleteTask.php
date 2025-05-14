<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetAddressDTO;
use App\DTO\GetClientDTO;
use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\AddressKeyboard;
use App\Http\Telegraph\Keyboards\ClientKeyboard;
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
        $response = $this->expeditorApiService->getTaskList();
        Telegraph::message('Вот ваш список')->keyboard(CompleteTaskKeyboard::handle($response->trips))->send();

    }

    public function selectTripTask(string $tripId)
    {
        $response = $this->expeditorApiService->getTaskList();
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);

        Telegraph::message($this->formatTripDetails($trip))->keyboard(CompleteTaskKeyboard::createDetailsKeyboard($trip))
            ->send();
    }

    public function getAddressList(string $tripId)
    {
        $response = $this->expeditorApiService->getAddressList($tripId);

        Telegraph::message('Список адресов')->keyboard(AddressKeyboard::show($response->addresses, $tripId))->send();

    }


    /**
     * @throws \Exception
     */
    public function selectAddress(string $addressId, string $tripId)
    {
        $response = $this->expeditorApiService->getAddressList($tripId);
        $address = $this->expeditorApiService->getAddressById($addressId, $response->addresses);

        Telegraph::message($this->sendAddressCard($address))->keyboard(AddressKeyboard::createDetailsKeyboard($address, $tripId))->send();


    }
    public function arrivedToAddress(string $addressId, string $tripId)
    {
        $addresses = $this->expeditorApiService->getAddressList($tripId);
        $address = $this->expeditorApiService->getAddressById($addressId, $addresses->addresses);

        $this->expeditorApiService->arrivedToAddress($tripId, $address->address);
    }
    public function leftAtTheAddress(string $addressId, string $tripId)
    {
        $addresses = $this->expeditorApiService->getAddressList($tripId);
        $address = $this->expeditorApiService->getAddressById($addressId, $addresses->addresses);

        $this->expeditorApiService->leftAtTheAddress($tripId, $address->address);
    }
    public function getClientListByAddress(string $addressId, string $tripId)
    {
        $addresses = $this->expeditorApiService->getAddressList($tripId);
        $address = $this->expeditorApiService->getAddressById($addressId, $addresses->addresses);

        $clientList = $this->expeditorApiService->getClientList($tripId, $address->address);
        Telegraph::message('Список клиентов по данному адресу')->keyboard(ClientKeyboard::show($clientList->clients, $tripId, $addressId))->send();
//        Telegraph::message('Список клиентов по данному адресу')->keyboard(ClientKeyboard::show($clientList->clients, $tripId, $addressId))->send();

    }

    public function selectClient(string $clientId, string $tripId, string $addressId)
    {
        $addresses = $this->expeditorApiService->getAddressList($tripId);
        $address = $this->expeditorApiService->getAddressById($addressId, $addresses->addresses);
        $clients = $this->expeditorApiService->getClientList($tripId, $address->address);

        $client = $this->expeditorApiService->getClientById($clientId, $clients->clients);
        Telegraph::message("{$client->clientName}")->send();
        Telegraph::message($this->sendClientCard($client))->send();
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

    protected function sendAddressCard(GetAddressDTO $address): string
    {
       return "🏢 *Клиент:* {$address->clientName}\n"
            . "📌 *Адрес:* {$address->address}\n"
            . "🕒 *Часы работы:* {$address->workHours}\n"
            . "📍 [Карта](https://yandex.ru/maps/?ll={$address->lon},{$address->lat})";

    }

    protected function sendClientCard(GetClientDTO $client): string
    {
        return "🏢 *Клиент:* {$client->clientName}\n"
            . "📌 *Количество поручений:* {$client->count}\n"
            . "🕒 *Тип поручений:* {$client->type}\n";

    }
}

