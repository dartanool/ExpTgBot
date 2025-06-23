<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\DTO\GetTtnTripDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\WarehouseTtnsKeyboard;
use App\Models\Telegraph\TelegraphUserLocation;
use DefStudio\Telegraph\Models\TelegraphChat;

//Приём со склада
class WarehouseAcceptance
{
    private ExpeditorApiService $expeditorApiService;
    private TelegraphChat $chat;
    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
        $this->expeditorApiService = new ExpeditorApiService($chat->chat_id);
    }
    public function handle(int $messageId, string $tripId)
    {
        $this->chat->deleteMessage($messageId)->send();
        $ttns = $this->expeditorApiService->acceptanceFromWarehouse($tripId);

        $this->chat->message("Список поручений по заданию {$tripId}")->keyboard(WarehouseTtnsKeyboard::show($ttns->trips, $tripId))->send();
    }

    public function selectTtnTrip(int $ttnId, string $tripId)
    {
        $ttns = $this->expeditorApiService->acceptanceFromWarehouse($tripId);
        $ttn = $this->expeditorApiService->getTtnTripById($ttnId, $ttns->trips);

        $this->chat->message($this->formatTtnTripDetails($ttn))->keyboard(WarehouseTtnsKeyboard::createDetailsKeyboard($ttn, $tripId))
            ->send();
    }

    public function moveByOrder(int $messageId, string $tripId, int $ttnTripId)
    {
        if ($this->checkLocation()) {
            $ttns = $this->expeditorApiService->acceptanceFromWarehouse($tripId);
            $ttn = $this->expeditorApiService->getTtnTripById($ttnTripId, $ttns->trips);

            $location = TelegraphUserLocation::query()->where('user_id', $this->chat->chat_id)->first();
            $this->expeditorApiService->moveByOrder($tripId, $ttn->idAexTtnTrip, $location->event_lat, $location->event_lon);
            $response = $this->chat->message("Вы нажали: Перемещение отправления на ТС по поручению")->send();
            sleep(3);
            $this->chat->deleteMessage($response->telegraphMessageId())->send();
        } else {
            $this->chat->message('Необходимо выполнить команду 📍 Отправить местоположение')->send();
        }
    }

    public function finishAcceptation(int $messageId, string $tripId)
    {
        if ($this->checkLocation()) {
            $response = $this->expeditorApiService->finishAcceptation($tripId, $location->event_lat, $location->event_lon);
            $response = $this->chat->message("Вы нажали: Окончил прием")->send();
            sleep(3);
            $this->chat->deleteMessage($response->telegraphMessageId())->send();
        } else {
            $this->chat->message('Необходимо выполнить команду 📍 Отправить местоположение')->send();
        }
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
    private function checkLocation() : bool
    {
        $location = TelegraphUserLocation::query()->where('user_id', $this->chat->chat_id)->first();

        if ($location->event_lat & $location->event_lon && $location->station_id) {
            return true;
        }
        return false;
    }

}
