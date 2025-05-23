<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetAddressDTO;
use App\DTO\GetClientDTO;
use App\DTO\GetTaskDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\AddressKeyboard;
use App\Http\Telegraph\Keyboards\ClientKeyboard;
use App\Http\Telegraph\Keyboards\CompleteTaskKeyboard;
use App\Models\Telegraph\TelegraphUserLocation;
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

    public function handle(string $tripId)
    {
        $location = TelegraphUserLocation::query()->where('user_id', $this->userId)->first();
        // Проверка на наличие долготы и широты
        $this->expeditorApiService->completeTask($tripId, $this->getLocation()->event_lat, $this->getLocation()->event_lon);

        $response = $this->expeditorApiService->getTaskList();
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);

        Telegraph::message($this->formatTripDetails($trip))->keyboard(CompleteTaskKeyboard::createDetailsKeyboard($trip))
            ->send();
    }

    public function getAddressList(int $messageId, string $tripId)
    {
        $response = $this->expeditorApiService->getAddressList($tripId);
        Telegraph::deleteMessage($messageId)->send();

        Telegraph::message("Список адресов по заданию: $tripId")->keyboard(AddressKeyboard::show($response->addresses, $tripId))->send();
    }

    /**
     * @throws \Exception
     */
    public function selectAddress(int $messageId,  string $addressId, string $tripId)
    {
        Telegraph::deleteMessage($messageId)->send();

        $address = $this->expeditorApiService->getAddressByAddressIdTripId($addressId, $tripId);
        Telegraph::message($this->sendAddressCard($address))->keyboard(AddressKeyboard::createDetailsKeyboard($address, $tripId))->send();
    }
    public function leftAtAddress(string $addressId, string $tripId)
    {

        $address = $this->expeditorApiService->getAddressByAddressIdTripId($addressId, $tripId);

        $this->expeditorApiService->leftAtAddress($tripId, $this->getLocation()->event_lat, $this->getLocation()->event_lon,$address->address);
    }
    public function arrivedToAddress(string $addressId, string $tripId)
    {
        $address = $this->expeditorApiService->getAddressByAddressIdTripId($addressId, $tripId);

        $this->expeditorApiService->arrivedToAddress($tripId, $this->getLocation()->event_lat, $this->getLocation()->event_lon, $address->address);
    }



    public function getClientListByAddress(string $addressId, string $tripId)
    {
        $address = $this->expeditorApiService->getAddressByAddressIdTripId($addressId, $tripId);

        $clientList = $this->expeditorApiService->getClientList($tripId, $address->address);

        Telegraph::message("Список клиентов по адресу: $address->address")->keyboard(ClientKeyboard::handle($clientList->clients, $addressId, $tripId))->send();

    }

    public function selectClient(string $clientName, string $addressId)
    {
        $cityId = TelegraphUserLocation::query()->where('user_id', $this->userId)->first();

        $tripId = $this->expeditorApiService->getCurrentTask($cityId->city_id);

        $address = $this->expeditorApiService->getAddressByAddressIdTripId($addressId, $tripId);

        $clients = $this->expeditorApiService->getClientList($tripId, $address->address);
        $client = $this->expeditorApiService->getClientByName($clientName, $clients->clients);

        Telegraph::message($this->sendClientCard($client))->send();
    }



    private function getLocation()
    {
        return TelegraphUserLocation::query()->where('user_id', $this->userId)->first();
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

