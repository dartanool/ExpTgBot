<?php

namespace App\Http\Telegraph\Handlers;

use App\Http\Services\TelegraphService;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use \DefStudio\Telegraph\Facades\Telegraph;
use \App\Http\Telegraph\API\GetTaskListAPI;

class GetTaskList
{
    private TelegraphService $telegraphService;
    private GetTaskListAPI $getTaskListAPI;
    private int $userId;

    public function __construct(int $chatId)
    {
        $this->telegraphService = new TelegraphService($chatId);
        $this->getTaskListAPI = new GetTaskListAPI();
        $this->userId = $chatId;
    }
    public function handle()
    {

        $response = $this->getTaskListAPI->handle($this->userId);


        Telegraph::message('Вот ваш список')->keyboard(TaskListKeyboard::handle($response->trips)) ->send();




//        if (isset($response)) {
//            $this->telegraphService->sendMessage('lf');
//        }
//        $this->telegraphService->sendMessage('no');
//        $this->telegraphService->sendApiResponse($response);
    }
}
