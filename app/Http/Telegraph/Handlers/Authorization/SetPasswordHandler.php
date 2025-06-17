<?php

namespace App\Http\Telegraph\Handlers\Authorization;

use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Models\TelegraphChat;

class SetPasswordHandler
{
    public TelegraphChat $chat;
    public function __construct(TelegraphChat $chat){
        $this->chat = $chat;
    }
    public function handle(string $password) : void
    {
        $data = TelegraphUserState::query()->where('user_id', $this->chat->chat_id)->first();

        (new CompleteAuthHandler($this->chat))->handle($data->data, $password);
    }
}
