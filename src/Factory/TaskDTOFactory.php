<?php

namespace App\Factory;

use App\DTO\TaskDTO;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;

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