<?php

namespace App\DTO;

use App\Entity\User;

class TaskDTO
{

    // New way of construct
    public function __construct(
        public int $id,
        public string $description,
        public bool $completed,
        public \DateTimeImmutable $createdAt,
        public ?User $user ) { }

    //old way of construct
//    public int $id;
//    public string $description;
//    public bool $completed;

//    public function __construct(Task $task) {
//        $this->id = $task->getId();
//        $this->description = $task->getDescription();
//        $this->completed = $task->getCompleted();
//    }
}