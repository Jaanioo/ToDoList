<?php

namespace App\Service;

use App\Entity\Task;
use App\Exception\TaskNotFoundException;
use App\Factory\TaskDTOFactory;
use App\Interface\TaskRepositoryInterface;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;

class TaskService
{

    //old PHP version
//    private TaskDTOFactory $taskDTOFactory;
//    private TaskRepository $repository;
    public function __construct(
        private readonly TaskDTOFactory $taskDTOFactory,
        private readonly TaskRepository $repository)
    {

        //old PHP version
//        $this->taskDTOFactory = $taskDTOFactory;
//        $this->repository = $repository;
    }

    public function getAllTaskDTO(TaskRepositoryInterface $taskRepository): array
    {
        // Use TaskRepository instead ManagerRegistry because it's more specified
        //$tasks = $this->repository->findAll();
        $tasks = $taskRepository->findAll();

        $data = [];

        foreach ($tasks as $task)
        {
           $data[] = $this->taskDTOFactory->getDTOFromTask($task);
        }

        return $data;
    }

    /**
     * @throws TaskNotFoundException
     */
    public function getSingleTaskDTO(int $id, TaskRepositoryInterface $taskRepository): object
    {
        //$task = $this->repository->find($id);
        $task = $taskRepository->find($id);

        if (!$task)
        {
            throw new TaskNotFoundException($id);
        }

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    public function newTaskDTO(Request $request, TaskRepositoryInterface $taskRepository): object
    {
        $task = new Task();
        $task->setDescription($request->get('description'));
        $task->setCompleted($request->get('completed'));

        //$this->repository->save($task, true);
        $taskRepository->save($task, true);

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    /**
     * @throws TaskNotFoundException
     * @throws \JsonException
     */
    public function editTaskDTO(Request $request, int $id, TaskRepositoryInterface $taskRepository): object
    {
        //$task = $this->repository->find($id);
        $task = $taskRepository->find($id);

        if (!$task)
        {
            throw new TaskNotFoundException($id);
        }

        $parametr = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $task->setDescription($parametr['description']);
        $task->setCompleted($parametr['completed']);
        //$this->repository->save($task, true);
        $taskRepository->save($task, true);

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    /**
     * @throws TaskNotFoundException
     */
    public function deleteTaskDTO(int $id, TaskRepositoryInterface $taskRepository): int
    {
        //$task = $this->repository->find($id);
        $task = $taskRepository->find($id);

        if (!$task)
        {
            throw new TaskNotFoundException($id);
        }

        //$this->repository->remove($task, true);
        $taskRepository->remove($task, true);

        return $id;
    }
}