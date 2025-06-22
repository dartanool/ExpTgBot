<?php

namespace App\DTO;

class CompleteTaskGetTtnTripListDTO
{
    public function __construct(
        public bool $success,
        /** @var CompleteTaskGetTtnTripDTO[] */
        public array $trips)
    {}
}
