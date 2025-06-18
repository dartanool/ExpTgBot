<?php

namespace App\Http\Telegraph\Handlers;

use App\Http\Telegraph\Handlers\Authorization\LogOut;
use App\Http\Telegraph\Handlers\Authorization\SetLoginHandler;
use App\Http\Telegraph\Handlers\Authorization\SetPasswordHandler;
use App\Http\Telegraph\Handlers\Location\SetLocation;
use App\Http\Telegraph\Handlers\Location\SetStation;
use App\Http\Telegraph\Keyboards\StartKeyboard;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;


class TelegramHandler extends WebhookHandler
{

    public SetLoginHandler $setLogin;
    public SetPasswordHandler  $setPassword;
    public SetLocation $setLocation;
    public SetStation $setStation;
    public GetTaskList $getTaskList;
    public WarehouseAcceptance $warehouseAcceptance;
    public CompleteTask $completeTask;
    public FinishTask $finishTask;
    public LogOut $logOut;
    public function initDependencies()
    {
        $this->setLogin = new SetLoginHandler($this->chat);
        $this->setPassword = new SetPasswordHandler($this->chat);
        $this->setLocation = new SetLocation($this->chat);
        $this->setStation = new SetStation($this->chat);

        $this->getTaskList = new GetTaskList($this->chat);
        $this->warehouseAcceptance = new WarehouseAcceptance($this->chat);
        $this->completeTask = new CompleteTask($this->chat);
        $this->finishTask = new FinishTask($this->chat);
        $this->logOut = new LogOut($this->chat);
    }

    protected function getUserId()
    {
        return $this->chat->chat_id;
    }

    public function start(): void
    {
        Log::info('Запрос',[
            'chat_id' => $this->chat->id,
            'chat' =>  $this->chat,
        ]);
        $this->chat->message('Добро пожаловать. Вам необходимо авторизоваться.')->keyboard(StartKeyboard::handle())->send();
    }
    public function auth(): void
    {
        TelegraphUserState::query()->updateOrCreate(
            ['user_id' => $this->getUserId()],
            ['state' => 'awaiting_login', 'data' => null]
        );
        $this->chat->message(' Введите сначала логин. Пример: Иванов И.В.')->send();
    }
    public function help(): void
    {
        $text = "👋 Добро пожаловать! Обратите внимание:\n"
            . "Доступ к информации имеют только <b>авторизованные пользователи</b>.\n\n"
            . "Для корректной работы выполните следующие шаги:\n"
            . "1️⃣ Нажмите кнопку «Установить станцию»\n"
            . "2️⃣ Затем нажмите «Определить местоположение»\n"
            . "3️⃣ После этого станет доступна кнопка «Список заданий»\n\n"
            . "⚠️ Важно:\n"
            . "- Следуйте шагам строго по порядку.\n"
            . "- Только после выполнения первых двух шагов вы сможете просматривать задания.\n\n"
            . "🔒 По окончании работы нажмите кнопку «Выйти» для завершения сессии.\n\n"
            . "Если возникнут вопросы, обращайтесь в службу поддержки:\n"
            . "📞 Телефон поддержки: +7 (123) 456-78-90\n"
            . " Сайт компании: https://www.jde.ru/";


        $this->chat->message($text)->send();
    }

//СПИСОК ЗАДАНИЙ
    public function showTripsList()
    {
        $this->initDependencies();
        $this->getTaskList->handle();
    }
    public function selectTrip()
    {
        $this->initDependencies();
        $this->getTaskList->selectTrip($this->messageId, $this->data->get('tripId'));
    }
//ПРИЁМ СО СКЛАДА

    public function acceptanceFromWarehouse()
    {
        $this->initDependencies();

        $this->warehouseAcceptance->handle($this->messageId, $this->data->get('tripId'));
    }

    public function selectTtnTrip()
    {
        $this->initDependencies();

        $this->warehouseAcceptance->selectTtnTrip( $this->data->get('ttnId'), $this->data->get('tripId'));

    }
    public function moveByOrder()
    {
        $this->initDependencies();

        $this->warehouseAcceptance->moveByOrder($this->data->get('tripId'),  $this->data->get('ttnTripId'));
    }
    //
    public function completeAcceptation()
    {
        $this->initDependencies();

        $this->warehouseAcceptance->completeAcceptation($this->data->get('tripId'), $this->data->get('ttnTripId'));
    }
    public function finishAcceptation()
    {
        $this->initDependencies();

        $this->warehouseAcceptance->finishAcceptation($this->data->get('tripId'));
    }


//ВЫПОЛНЕНИЕ ЗАДАНИЯ
    public function completeTask()
    {
        $this->initDependencies();

        $this->completeTask->handle($this->messageId ,$this->data->get('tripId'));
    }
    public function getAddressList()
    {
        $this->initDependencies();
        $this->completeTask->getAddressList($this->messageId,$this->data->get('tripId'));
    }
    public function selectAddress()
    {
        $this->initDependencies();
        $this->completeTask->selectAddress($this->messageId, $this->data->get('addressId'),$this->data->get('tripId'));
    }

    public function arrivedToAddress()
    {
        $this->initDependencies();
        $this->completeTask->arrivedToAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function leftAtAddress()
    {
        $this->initDependencies();
        $this->completeTask->leftAtAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function getClientList()
    {
        $this->initDependencies();
        $this->completeTask->getClientListByAddress($this->data->get('addressId'),$this->data->get('tripId'));
    }
    public function selectClient()
    {
        $this->initDependencies();
        $this->completeTask->selectClient($this->data->get('clientName'), $this->data->get('addressId') );
    }


    //ЗАВЕРШИТЬ ЗАДАНИЕ
    public function finishTask()
    {
        $this->initDependencies();
        $this->finishTask->handle($this->messageId ,$this->data->get('tripId'));
    }
    public function arrivedToUnload()
    {
        $this->initDependencies();
        $this->finishTask->arrivedToUnload($this->data->get('tripId'));

    }
    public function completeDelivery()
    {
        $this->initDependencies();
        $this->finishTask->completeDelivery($this->data->get('tripId'));
    }
    public function submitVehicleAndDocuments()
    {
        $this->initDependencies();
        $this->finishTask->submitVehicleAndDocuments($this->data->get('tripId'));
    }



    public function handleChatMessage(Stringable $text): void
    {
        $this->initDependencies();

        $userId =$this->getUserId();

        if ($this->message->location()) {
            $this->setLocation->handleLocation($this->message->location());
        }

        $userState = TelegraphUserState::query()->where('user_id', $userId)->first();

        if ($userState){
            switch ($userState->state) {
                case 'awaiting_login':
                    $this->setLogin->handle($text->toString());
                    break;
                case 'awaiting_password':
                    $this->setPassword->handle($text->toString());
                    break;
                case 'awaiting_city':
                    $this->setLocation->setCity($text->toString());
                    break;
                case 'awaiting_station':
                    $this->setStation->handle($text->toString());
                    break;
            }
        }

        switch ($text->toString()) {
            case 'Список заданий':
                $this->getTaskList->handle();
                break;
            case 'Установить станцию' :
                $this->setLocation->location();
                break;
            case 'Выйти' :
                $this->logOut->handle();
                break;
        }
    }
}
