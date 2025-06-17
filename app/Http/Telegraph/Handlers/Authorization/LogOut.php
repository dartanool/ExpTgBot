<?php

namespace App\Http\Telegraph\Handlers\Authorization;

use App\Models\Telegraph\TelegraphUserLocation;
use App\Models\Telegraph\TelegraphUsers;
use DefStudio\Telegraph\Models\TelegraphChat;

class LogOut
{
    public TelegraphChat $chat;
    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
    }
    public function handle()
    {
        TelegraphUserLocation::query()->where('user_id',$this->chat->chat_id)->delete();
        TelegraphUsers::query()->where('user_id',$this->chat->chat_id)->delete();
        $this->chat->message('Вы вышли ')->removeReplyKeyboard()->send();
    }
}


