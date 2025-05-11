<?php

namespace App\DTO;

class GetTasksListDTO
{
    public function __construct(
        public bool $success,
        /** @var GetTaskDTO[] */
        public array $tasks,
    ) {}


}
