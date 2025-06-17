<?php

namespace App\DTO;

class GetClientDTO
{
    public function __construct(
        public string $id,
        public readonly string $clientName,
        public readonly int $count,
        public readonly string $type,
    ) {}
}
