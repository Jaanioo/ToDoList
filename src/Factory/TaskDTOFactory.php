<?php

namespace App\Factory;

use App\DTO\TaskDTO;
use App\Entity\Task;

class TaskDTOFactory
{

    public function getDTOFromTask(Task $task): TaskDTO
    {
        return new TaskDTO(
            $task->getId(),
            $task->getDescription(),
            $task->getCompleted()
        );
    }
}