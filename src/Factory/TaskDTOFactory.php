<?php

namespace App\Factory;

use App\DTO\TaskDTO\TaskDTO;
use App\Entity\Task;

class TaskDTOFactory
{
    public function getDTOFromTask(Task $task): TaskDTO
    {
        return new TaskDTO(
            $task->getId(),
            $task->getDescription(),
            $task->getCompleted(),
            $task->getCreatedAt(),
            $task->getUser()
        );
    }
}
