<?php

namespace App\Http\Telegraph\Handlers;

use App\Http\Telegraph\Handlers\Authorization\SetLoginHandler;
use App\Http\Telegraph\Handlers\Authorization\SetPasswordHandler;
use App\Http\Telegraph\Handlers\Location\SetLocation;
use App\Http\Telegraph\Handlers\Location\SetStation;
use App\Http\Telegraph\Keyboards\AddressKeyboard;
use App\Http\Telegraph\Keyboards\CompleteTaskKeyboard;
use App\Http\Telegraph\Keyboards\StartKeyboard;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Stringable;


class TelegramHandler extends WebhookHandler
{


    public function start(): void
    {
        Telegraph::message('Добро пожаловать. Вам необходимо авторизоваться.')
            ->keyboard(StartKeyboard::handle())->send();
    }


    public function auth(): void
    {
        $userId = $this->chat->chat_id;

        TelegraphUserState::query()->updateOrCreate(
            ['user_id' => $userId],
            ['state' => 'awaiting_login', 'data' => null]
        );
        Telegraph::message(' Введите сначала логин. Пример: Иванов И.В.')->send();

    }

//СПИСОК ЗАДАНИЙ
    public function showTripsList()
    {
        $userId = $this->chat->chat_id;

        // Возвращаем пользователю исходный список
        (new GetTaskList($userId))->handle();
    }
    public function selectTrip()
    {
        $userId = $this->chat->chat_id;

        (new GetTaskList($userId))->selectTrip($this->data->get('tripId'));

    }
//ПРИЁМ СО СКЛАДА
    public function selectTripWareHouse()
    {
        $userId = $this->chat->chat_id;

        (new WarehouseAcceptance($userId))->selectTripWareHouse($this->data->get('tripId'));

    }
    public function completeAcceptation()
    {
        $userId = $this->chat->chat_id;
        (new WarehouseAcceptance($userId))->completeAcceptation($this->data->get('tripId'));
    }

    public function cancelEvent()
    {
        $userId = $this->chat->chat_id;
        (new WarehouseAcceptance($userId))->cancelEvent($this->data->get('tripId'));
    }

    public function finishAcceptation()
    {
        $userId = $this->chat->chat_id;
        (new WarehouseAcceptance($userId))->finishAcceptation($this->data->get('tripId'));

    }

//ВЫПОЛНЕНИЕ ЗАДАНИЯ

    public function selectTripTask()
    {
        $userId = $this->chat->chat_id;

        (new CompleteTask($userId))->selectTripTask($this->data->get('tripId'));
    }
    public function getAddressList()
    {
        $userId = $this->chat->chat_id;

        (new CompleteTask($userId))->getAddressList($this->data->get('tripId'));

    }
    public function selectAddress()
    {
        $userId = $this->chat->chat_id;
        (new CompleteTask($userId))->selectAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }

    public function arrivedToAddress()
    {
        $userId = $this->chat->chat_id;
        (new CompleteTask($userId))->arrivedToAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function leftAtTheAddress()
    {
        $userId = $this->chat->chat_id;
        (new CompleteTask($userId))->leftAtTheAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function getClientList()
    {
        $userId = $this->chat->chat_id;
        (new CompleteTask($userId))->getClientListByAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function selectClient()
    {
        $userId = $this->chat->chat_id;
        (new CompleteTask($userId))->selectClient($this->data->get('clientId'),$this->data->get('tripId'),$this->data->get('addressId') );
    }


    public function handleChatMessage(Stringable $text): void
    {

        $userId = $this->message->from()->id();
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
            case 'Приём со склада' :
                (new WarehouseAcceptance($userId))->handle();
                break;
            case 'Выполнение задания' :
                (new CompleteTask($userId))->handle();

        }
    }








}
