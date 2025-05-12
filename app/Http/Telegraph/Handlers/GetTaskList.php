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

    public function __construct(int $chatId)
    {
        $this->telegraphService = new TelegraphService($chatId);
        $this->getTaskListAPI = new GetTaskListAPI();
    }
    public function handle(int $userId)
    {

        $response = $this->getTaskListAPI->handle($userId);


        Telegraph::message('Вот ваш список')->keyboard(TaskListKeyboard::handle($response->trips)) ->send();




//        if (isset($response)) {
//            $this->telegraphService->sendMessage('lf');
//        }
//        $this->telegraphService->sendMessage('no');
//        $this->telegraphService->sendApiResponse($response);
    }
}
