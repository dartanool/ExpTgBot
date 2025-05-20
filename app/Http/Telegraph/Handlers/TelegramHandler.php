<?php

namespace App\Http\Telegraph\Handlers;

use App\Http\Telegraph\Handlers\Authorization\SetLoginHandler;
use App\Http\Telegraph\Handlers\Authorization\SetPasswordHandler;
use App\Http\Telegraph\Handlers\Location\SetLocation;
use App\Http\Telegraph\Handlers\Location\SetStation;
use App\Http\Telegraph\Keyboards\StartKeyboard;
use App\Models\Telegraph\TelegraphUserLocation;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;


class TelegramHandler extends WebhookHandler
{

    protected function getUserId()
    {
        return $this->chat->chat_id;
    }

    public function start(): void
    {
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
        (new GetTaskList($this->getUserId()))->selectTrip($this->messageId, $this->data->get('tripId'));

    }
//ПРИЁМ СО СКЛАДА

    public function acceptanceFromWarehouse()
    {
        (new WarehouseAcceptance($this->getUserId()))->handle($this->messageId, $this->data->get('tripId'));
    }
    public function markAsRead()
    {
        (new WarehouseAcceptance($this->getUserId()))->markAsRead($this->data->get('tripId'));

    }
    public function moveByOrder()
    {
        (new WarehouseAcceptance($this->getUserId()))->moveByOrder($this->data->get('tripId'));

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
        (new CompleteTask($this->getUserId()))->getAddressList($this->messageId,$this->data->get('tripId'));
    }
    public function selectAddress()
    {
        (new CompleteTask($this->getUserId()))->selectAddress($this->messageId, $this->data->get('addressId'),$this->data->get('tripId'));
    }

    public function arrivedToAddress()
    {
        (new CompleteTask($this->getUserId()))->arrivedToAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function leftAtAddress()
    {
        (new CompleteTask($this->getUserId()))->leftAtAddress($this->data->get('addressId'),$this->data->get('tripId'));
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

        if ($this->message->location()) {
            $this->handleLocation();
        }

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



    public function handleLocation(): void
    {
        $userId = $this->getUserId();

        $location = $this->message->location();

        TelegraphUserLocation::query()->where('user_id', $userId)->update(
            [
                'event_lat' => $location->latitude(),
                'event_lon' => $location->longitude()
            ]
        );

    }





}
