<?php

namespace App\DTO\TaskDTO;

use App\Entity\User;

class TaskDTO
{
    // New way of construct
    public function __construct(
        public ?int $id,
        public string $description,
        public bool $completed,
        public \DateTimeImmutable $createdAt,
        public ?User $user
    ) {
    }
}
