<?php

namespace App\Http\Telegraph\Handlers;

use App\Models\Telegraph\TelegramUserState;
use App\Models\Telegraph\TelegraphUsers;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\EmptyWebhookHandler;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Type\CallableType;
use Illuminate\Support\Stringable;


class TelegramHandler extends WebhookHandler
{
    private string $apiToken;
    private string $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->apiToken = env('API_TOKEN');;
        $this->baseUrl = env('API_BASE_URL');
    }


    public function start(): void
    {
        $button = Button::make('Авторизация')->action('auth');
        $keyboard = Keyboard::make()->buttons([$button]);

        Telegraph::message('Добро пожаловать. Вам необходимо авторизоваться.')->keyboard($keyboard)->send();
    }


    public function auth()
    {
        $this->setLogin();

    }

    private function setLogin()
    {
        $this->reply("login");

        $userId = $this->message->from()->id();

        TelegramUserState::query()->updateOrCreate(
            ['user_id' => $userId],
            ['state' => 'awaiting_login', 'data' => null]
        );
        Telegraph::message(' Введите сначала логин. Пример: Иванов И.В.')->send();

    }

    private function setPassword(string $login)
    {
        $userId = $this->message->from()->id();

        TelegramUserState::query()->updateOrCreate(
            ['user_id' => $userId],
            ['state' => 'awaiting_password', 'data' => $login]
        );

        Telegraph::message(' Введите пароль без пробелов.')->send();

    }

    private function completeAuth(string $login, string $password): void
    {
        $pragma = $this->getSession($login, $password);
        $this->reply("Вы успешно авторизовались $pragma");

//        $userId = $this->chat->chat_id;
        $userId = $this->message->from()->id();

        if ($pragma)
        {
            $this->reply("Вы успешно авторизовались");

            TelegraphUsers::query()->create([ //updateOrCreate
                'user_id' => $userId,
                'token' =>$pragma,
            ]);
        }

    }
    public function handleChatMessage(Stringable $text): void
    {

        $userId = $this->message->from()->id();
        $userState = TelegramUserState::query()->where('user_id', $userId)->first();


        if (!$userState){
            $this->reply("Нажмите /start");
            return ;
        }

        switch ($userState->state) {
            case 'awaiting_login':
                //
                $this->setPassword($text->toString());
                break;
            case 'awaiting_password':
                //
                $this->completeAuth($userState->data ,$text->toString());
                break;
        }
    }






    private function getSession(string $login, string $password): string
    {

        $data = ['Username' => $login, 'Password' => $password];

        $response = retry(3,function () use ($data){
            return Http::withHeaders([
                'Authorization' => 'Basic '. $this->apiToken ,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'GetSession', $data);
        });

        if (!$response){
            throw new \Exception('Api request failed', $response->status());
        }

        $data = $response->json();

        return $data['Pragma'];

    }

}
