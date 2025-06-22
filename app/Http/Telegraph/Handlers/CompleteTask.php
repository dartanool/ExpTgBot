<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\CompleteTaskGetTtnTripDTO;
use App\DTO\GetAddressDTO;
use App\DTO\GetClientDTO;
use App\DTO\GetTaskDTO;
use App\DTO\GetTtnTripDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\AddressKeyboard;
use App\Http\Telegraph\Keyboards\ClientKeyboard;
use App\Http\Telegraph\Keyboards\CompleteTaskKeyboard;
use App\Http\Telegraph\Keyboards\WarehouseTtnsKeyboard;
use App\Models\Telegraph\TelegraphUserLocation;
use DefStudio\Telegraph\Models\TelegraphChat;

class CompleteTask
{
    private TelegraphChat $chat;
    private ExpeditorApiService $expeditorApiService;

    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
        $this->expeditorApiService = new ExpeditorApiService($chat->chat_id);
    }

    public function handle(int $messageId, string $tripId)
    {
        $this->chat->deleteMessage($messageId)->send();

        $this->expeditorApiService->completeTask($tripId, $this->getLocation()->event_lat, $this->getLocation()->event_lon);

        $response = $this->expeditorApiService->getTaskList();
        $trip = $this->expeditorApiService->getTripById($tripId, $response->trips);
        $this->chat->message($this->formatTripDetails($trip))->keyboard(CompleteTaskKeyboard::createDetailsKeyboard($trip))->send();
    }

    public function getAddressList(int $messageId, string $tripId)
    {
        $response = $this->expeditorApiService->getAddressList($tripId);
        $this->chat->deleteMessage($messageId)->send();

        $this->chat->message("Список адресов по заданию: $tripId")->keyboard(AddressKeyboard::show($response->addresses, $tripId))->send();
    }

    /**
     * @throws \Exception
     */
    public function selectAddress(int $messageId,  string $addressId, string $tripId)
    {
        $this->chat->deleteMessage($messageId)->send();

        $address = $this->expeditorApiService->getAddressByAddressIdTripId($addressId, $tripId);
        $this->chat->message($this->sendAddressCard($address))->keyboard(AddressKeyboard::createDetailsKeyboard($address, $tripId))->send();
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

        $this->chat->message("Список клиентов по адресу: $address->address")->keyboard(ClientKeyboard::handle($clientList->clients, $addressId, $tripId))->send();
    }

    public function selectClient(string $clientId, string $addressId)
    {
        $cityId = TelegraphUserLocation::query()->where('user_id', $this->chat->chat_id)->first();

        $tripId = $this->expeditorApiService->getCurrentTask($cityId->city_id);

        $address = $this->expeditorApiService->getAddressByAddressIdTripId($addressId, $tripId);

        $clients = $this->expeditorApiService->getClientList($tripId, $address->address);
        $client = $this->expeditorApiService->getClientById($clientId, $clients->clients);

        $ttns = $this->expeditorApiService->getTtnsByAddressClient($tripId, $client->clientName, $address->address);

        $data = "{$client->id}/{$addressId}";
        $this->chat->message("{$data}")->send();

        foreach ($ttns->trips as $ttn) {
            $ttn->setClient($client);
        }
        $this->chat->message($this->sendClientCard($client))->keyboard(CompleteTaskKeyboard::buildTripOrdersKeyboard($ttns->trips,$data))->send();
    }
    public function selectTtnTrip( string $data, int $ttnId)
    {
        $cityId = TelegraphUserLocation::query()->where('user_id', $this->chat->chat_id)->first();
        $tripId = $this->expeditorApiService->getCurrentTask($cityId->city_id);

        $data = explode("/", $data);
        $clientId = $data[0];
        $addressId = $data[1];

        $address = $this->expeditorApiService->getAddressByAddressIdTripId($addressId, $tripId);
        $clients = $this->expeditorApiService->getClientList($tripId, $address->address);
        $client = $this->expeditorApiService->getClientById($clientId, $clients->clients);

        $ttns = $this->expeditorApiService->getTtnsByAddressClient($tripId, $client->clientName, $address->address);
        $ttn = $this->expeditorApiService->getTtnTripById($ttnId, $ttns->trips);



        $this->chat->message($this->sendTripOrderCard($ttn))->keyboard(CompleteTaskKeyboard::createDetailsKeyboardForEvent($ttn)) ->send();
    }
    //Получение отправления (ТТН) по поручению
    public function setTtnStatusReceived(int $ttnId)
    {
       $this->expeditorApiService->setTtnStatusReceived($ttnId,$this->getLocation()->event_lat, $this->getLocation()->event_lon);
    }
    //Выдача отправления (ТТН) по поручению
    public function setTtnStatusIssued(int $ttnId)
    {
        $this->expeditorApiService->setTtnStatusIssued($ttnId,$this->getLocation()->event_lat, $this->getLocation()->event_lon);
    }
    public function failOrder()
    {
//        $this->chat->message("Выберите причину")->keyboard(CompleteTaskKeyboard::)
    }

    private function getLocation()
    {
        return TelegraphUserLocation::query()->where('user_id', $this->chat->chat_id)->first();
    }
    private function formatTripDetails(GetTaskDTO $trip): string
    {
        return <<<TEXT
        🚛 Детали задания #{$trip->id}*

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

    protected function sendAddressCard(GetAddressDTO $address): string
    {
       return "🏢 Клиент: {$address->clientName}\n\n"
            . "📌 Адрес: {$address->address}\n\n"
            . "🕒 Часы работы: {$address->workHours}\n"
            . "📍 [Карта](https://yandex.ru/maps/?ll={$address->lon},{$address->lat})";

    }

    protected function sendClientCard(GetClientDTO $client): string
    {
        return "🏢 Клиент: {$client->clientName}\n"
            . "📌 Количество поручений: {$client->count}\n"
            . "🕒 Тип поручений: {$client->type}\n";

    }
    private function formatTtnTripDetails(GetTtnTripDTO $trip): string
    {
        $contactParts = explode(':', $trip->aexoTel);
        $phone = $contactParts[0] ?? '';
        $contactName = $contactParts[1] ?? 'не указано';

        return <<<TEXT
            📦 Детали поручения #{$trip->prchStrNom}

            ID поручения: {$trip->idAexTtnTrip}
            ID заявки: {$trip->aexTtnTripIdRec}
            ID события: {$trip->idS72}

            Параметры груза:
            - Вес: {$trip->prchVes} кг
            - Объем: {$trip->prchObyom} м³
            - Клиентские места: {$trip->prchCliMest}
            - Багажные места: {$trip->prchBagMest}

            Контактное лицо:
            - Телефон: {$phone}
            - Имя: {$contactName}
            TEXT;
    }

    private function sendTripOrderCard(CompleteTaskGetTtnTripDTO $order): string
    {
        return "📋 Поручение ID: {$order->ID_AEX_TTNTRIP}\n\n"
            . "👤 Клиент и тип: {$order->CLIENT_TIP_NAME} {$order->AEX_TTNTRIP_TIP}\n\n"
            . "📞 Телефоны: {$order->AEXO_TEL}\n\n"
            . "🕒 Время работы: {$order->AEXO_TWORK_STOR}\n"
            . "⚖️ Вес: {$order->PRCH_VES} кг\n"
            . "📦 Объем: {$order->PRCH_OBYOM} м³\n"
            . "🎒 Клиентских мест: {$order->PRCH_CLI_MEST}\n"
            . "🧳 Багажных мест: {$order->PRCH_BAG_MEST}\n\n"
            . "📍 [Карта](https://yandex.ru/maps/?ll={$order->AEXO_LON_ADR},{$order->AEXO_LAT_ADR})";
    }

}

