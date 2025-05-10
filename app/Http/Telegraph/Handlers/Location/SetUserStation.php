<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Telegraph\Keyboards\TaskKeyboard;
use DefStudio\Telegraph\Facades\Telegraph;

class SetUserStation
{
    public function handle()
    {
        Telegraph::message("На какой станции вы находитесь")->replyKeyboard(TaskKeyboard::handle())->send();
    }
}
