<?php

namespace App\Http\Telegraph\Handlers;

use App\DTO\GetTaskDTO;
use App\Http\Services\ApiService;
use App\Http\Telegraph\API\GetTaskListAPI;
use App\Http\Telegraph\Handlers\Authorization\SetLoginHandler;
use App\Http\Telegraph\Handlers\Authorization\SetPasswordHandler;
use App\Http\Telegraph\Handlers\Location\SetLocation;
use App\Http\Telegraph\Handlers\Location\SetStation;
use App\Http\Telegraph\Keyboards\StartKeyboard;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
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

    public function selectTrip()
    {
        $userId = $this->chat->chat_id;

        $tripId = $this->data->get('tripId');
        $apiService = new ApiService();
        $getTaskListAPI = new GetTaskListAPI();


        $response = $getTaskListAPI->handle($userId);


        // Получаем данные задания (может быть из кэша или нового API-запроса)
        $trip = $apiService->getTripById($tripId, $response->trips);

        // Форматируем сообщение с деталями
        $message = $this->formatTripDetails($trip);

        // Отправляем сообщение с новой клавиатурой
        $this->chat->message($message)
            ->keyboard(TaskListKeyboard::createDetailsKeyboard($trip))
            ->send();
    }

    public function completeTrip()
    {
        $tripId = $this->data->get('tripId');
        // Логика подтверждения выполнения...
        $this->chat->message("✅ Задание #{$tripId} отмечено как выполненное")->send();
    }

    public function arriveTrip()
    {
        $tripId = $this->data->get('tripId');
        // Логика отметки прибытия...
        $this->chat->message("📍 Вы отметили прибытие по заданию #{$tripId}")->send();
    }

    public function showTripsList()
    {
        $userId = $this->chat->chat_id;

        // Возвращаем пользователю исходный список
        (new GetTaskList($userId))->handle($userId);
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
                (new GetTaskList($userId))->handle($userId);
                break;
            case 'Установить станцию' :
                (new SetLocation($userId))->location();
                break;

        }
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

}
