<?php

namespace App\Http\Telegraph\Handlers;

use App\Http\Services\ExpeditorApiService;
use App\Http\Services\TelegraphService;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use \DefStudio\Telegraph\Facades\Telegraph;
use \App\Http\Telegraph\API\GetTaskListAPI;

class GetTaskList
{
    private int $userId;
    private ExpeditorApiService $expeditorApiService;

    public function __construct(int $chatId)
    {
        $this->expeditorApiService = new ExpeditorApiService($chatId);

        $this->userId = $chatId;
    }
    public function handle()
    {

        $response = $this->expeditorApiService->getTaskList($this->userId);
        Telegraph::message('Вот ваш список')->keyboard(TaskListKeyboard::handle($response->trips)) ->send();


    }
}
