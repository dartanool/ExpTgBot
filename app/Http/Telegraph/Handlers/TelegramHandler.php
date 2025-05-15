<?php

namespace App\Http\Telegraph\Handlers;

use App\Http\Telegraph\Handlers\Authorization\SetLoginHandler;
use App\Http\Telegraph\Handlers\Authorization\SetPasswordHandler;
use App\Http\Telegraph\Handlers\Location\SetLocation;
use App\Http\Telegraph\Handlers\Location\SetStation;
use App\Http\Telegraph\Keyboards\StartKeyboard;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;


class TelegramHandler extends WebhookHandler
{
    protected ?\Closure $locationCallback = null;

    protected function getUserId()
    {
        return $this->chat->chat_id;
    }

    public function start(): void
    {
        $this->requestLocation(function(array $location) {
            // Этот код выполнится когда пользователь отправит местоположение
            $latitude = $location['latitude'];
            $longitude = $location['longitude'];

            // Продолжаем процесс доставки с полученными координатами
            $this->processDeliveryWithLocation($latitude, $longitude);
        });

        Telegraph::message("Добро пожаловать. {$this->message->location()->latitude()}")->send();
        Telegraph::message('Добро пожаловать. Вам необходимо авторизоваться.')
            ->keyboard(StartKeyboard::handle())->send();
    }


    public function auth(): void
    {
        TelegraphUserState::query()->updateOrCreate(
            ['user_id' => $this->getUserId()],
            ['state' => 'awaiting_login', 'data' => null]
        );
        Telegraph::message(' Введите сначала логин. Пример: Иванов И.В.')->send();
    }


//СПИСОК ЗАДАНИЙ
    public function showTripsList()
    {
        (new GetTaskList($this->getUserId()))->handle();
    }
    public function selectTrip()
    {
        (new GetTaskList($this->getUserId()))->selectTrip($this->data->get('tripId'));

    }
//ПРИЁМ СО СКЛАДА
    public function acceptanceFromWarehouse()
    {
        (new WarehouseAcceptance($this->getUserId()))->handle($this->data->get('tripId'));
    }
    //
    public function completeAcceptation()
    {

        (new WarehouseAcceptance($this->getUserId()))->completeAcceptation($this->data->get('tripId'));
    }

    public function cancelEvent()
    {
        (new WarehouseAcceptance($this->getUserId()))->cancelEvent($this->data->get('tripId'));
    }

    public function finishAcceptation()
    {
        (new WarehouseAcceptance($this->getUserId()))->finishAcceptation($this->data->get('tripId'));
    }


//ВЫПОЛНЕНИЕ ЗАДАНИЯ
    public function completeTask()
    {
        (new CompleteTask($this->getUserId()))->handle($this->data->get('tripId'));
    }
    public function getAddressList()
    {
        (new CompleteTask($this->getUserId()))->getAddressList($this->data->get('tripId'));
    }
    public function selectAddress()
    {
        (new CompleteTask($this->getUserId()))->selectAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }

    public function arrivedToAddress()
    {
        (new CompleteTask($this->getUserId()))->arrivedToAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function leftAtTheAddress()
    {
        (new CompleteTask($this->getUserId()))->leftAtTheAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function getClientList()
    {
        (new CompleteTask($this->getUserId()))->getClientListByAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function selectClient()
    {
        (new CompleteTask($this->getUserId()))->selectClient($this->data->get('clientName'), $this->data->get('addressId') );
    }


    //ЗАВЕРШИТЬ ЗАДАНИЕ
    public function finishTask()
    {
        (new FinishTask($this->getUserId()))->handle($this->data->get('tripId'));
    }
    public function arrivedToUnload()
    {
        (new FinishTask($this->getUserId()))->arrivedToUnload($this->data->get('tripId'));

    }
    public function completeDelivery()
    {
        (new FinishTask($this->getUserId()))->completeDelivery($this->data->get('tripId'));
    }
    public function submitVehicleAndDocuments()
    {
        (new FinishTask($this->getUserId()))->submitVehicleAndDocuments($this->data->get('tripId'));
    }



    public function handleChatMessage(Stringable $text): void
    {

        $userId =$this->getUserId();
        $userState = TelegraphUserState::query()->where('user_id', $userId)->first();

        if ($userState){
            switch ($userState->state) {
                case 'awaiting_login':
                    (new SetLoginHandler())->handle($userId, $text->toString());
                    break;
                case 'awaiting_password':
                    (new SetPasswordHandler())->handle($userId, $text->toString());
                    break;
                case 'awaiting_city':
                    Telegraph::message('password')->send();
                    (new SetLocation($userId))->setCity($text->toString());
                    break;
                case 'awaiting_station':
                    (new SetStation($userId))->handle($text->toString());
                    break;
            }
        }

        switch ($text->toString()) {
            case 'Список заданий':
                (new GetTaskList($userId))->handle();
                break;
            case 'Установить станцию' :
                (new SetLocation($userId))->location();
                break;
        }
    }


    public function requestLocation(\Closure $callback): void
    {
        // Сохраняем колбэк для последующего вызова
        $this->locationCallback = $callback;

        Telegraph::message('Пожалуйста, поделитесь своим местоположением')
            ->replyKeyboard([
                [['text' => '📍 Отправить местоположение', 'request_location' => true]]
            ])
            ->send();
    }
    public function handleLocation(): void
    {
        if (!$this->message?->location()) {
            return;
        }

        $location = $this->message->location();
        $coords = [
            'latitude' => $location->latitude(),
            'longitude' => $location->longitude(),
            'user_id' => $this->getUserId()
        ];

        // Вызываем колбэк если он был установлен
        if ($this->locationCallback) {
            call_user_func($this->locationCallback, $coords);
            $this->locationCallback = null; // Очищаем после вызова
        }

        Telegraph::removeReplyKeyboard()->send();
    }





}
