<?php

namespace App\Http\Telegraph\API;

use App\Http\Services\TelegraphService;
use App\Http\Telegraph\Handlers\GetTaskList;
use App\Http\Telegraph\Keyboards\TaskKeyboard;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use DefStudio\Telegraph\Facades\Telegraph;

class WareHouse
{
    private $getTaskListAPI;
    public function __construct()
    {
        $this->getTaskListAPI = new GetTaskListAPI();
    }

    public function handle(int $userId)
    {


        (new GetTaskList($userId))->handle();
    }

}
