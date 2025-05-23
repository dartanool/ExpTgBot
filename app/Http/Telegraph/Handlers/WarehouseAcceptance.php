<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\DTO\GetTtnTripDTO;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use App\Http\Telegraph\Keyboards\TtnsKeyboard;
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
        $ttns = $this->expeditorApiService->acceptanceFromWarehouse($tripId);
        Telegraph::deleteMessage($messageId)->send();

        Telegraph::message('ls')->keyboard(TtnsKeyboard::show($ttns->trips, $tripId))
            ->send();
    }

    public function selectTtnTrip(int $messageId, int $ttnId, string $tripId)
    {
        $ttns = $this->expeditorApiService->acceptanceFromWarehouse($tripId);
        $ttn = $this->expeditorApiService->getTtnTripById($ttnId, $ttns->trips);

        Telegraph::message($this->formatTtnTripDetails($ttn))->keyboard(TtnsKeyboard::createDetailsKeyboard($ttn, $tripId))
            ->send();
    }
    public function moveByOrder(string $tripId, int $ttnTripId)
    {
        $location = TelegraphUserLocation::query()->where('user_id', $this->userId)->first();
        $this->expeditorApiService->moveByOrder($tripId, $ttnTripId, $location->event_lat, $location->event_lon);
    }

    public function completeAcceptation(string $tripId, int $ttnTripId)
    {
        Telegraph::message('Для создания события необходимо выполнить команду 📍 Отправить местоположение')->send();

        $location = TelegraphUserLocation::query()->where('user_id', $this->userId)->first();

        if ($location->event_lat & $location->event_lon) {
            $response = $this->expeditorApiService->completeAcceptation($tripId, $ttnTripId, $location->event_lat, $location->event_lon);
            Telegraph::message('lf')->send();
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
        $location = TelegraphUserLocation::query()->where('user_id', $this->userId)->first();

        if ($location->event_lat & $location->event_lon) {
            $response = $this->expeditorApiService->finishAcceptation($tripId, $location->event_lat, $location->event_lon);
            Telegraph::message('lf')->send();
        } else {
            Telegraph::message('Необходимо выполнить команду 📍 Отправить местоположение')->send();
        }
        Telegraph::message('Finish acceptation')->send();
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

    private function formatTtnTripDetails(GetTtnTripDTO $trip): string
    {
        // Парсим телефон и имя контакта
        $contactParts = explode(':', $trip->aexoTel);
        $phone = $contactParts[0] ?? '';
        $contactName = $contactParts[1] ?? 'не указано';

        return <<<TEXT
            📦 *Детали поручения #{$trip->prchStrNom}*

            *ID поручения:* {$trip->idAexTtnTrip}
            *ID заявки:* {$trip->aexTtnTripIdRec}
            *ID события:* {$trip->idS72}

            *Параметры груза:*
            - Вес: {$trip->prchVes} кг
            - Объем: {$trip->prchObyom} м³
            - Клиентские места: {$trip->prchCliMest}
            - Багажные места: {$trip->prchBagMest}

            *Контактное лицо:*
            - Телефон: {$phone}
            - Имя: {$contactName}
            TEXT;
    }

}
