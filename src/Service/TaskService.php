<?php

namespace App\Service;

use App\Entity\Task;
use App\Exception\TaskNotFoundException;
use App\Factory\TaskDTOFactory;
use App\Interface\TaskRepositoryInterface;
use App\Interface\UserRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskService
{

    //old PHP version
//    private TaskDTOFactory $taskDTOFactory;
//    private TaskRepository $repository;
    public function __construct(
        private readonly TaskDTOFactory $taskDTOFactory,
        private readonly TaskRepositoryInterface $repository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly SecurityService $securityService)
    {

        //old PHP version
//        $this->taskDTOFactory = $taskDTOFactory;
//        $this->repository = $repository;
    }

    public function getAllTasksForUserDTO(Security $security): array
    {
        $userId = $this->securityService->getCurrentUserId($security);

        if ($userId === null) {
            return new JsonResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);

        if (!$user)
        {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $tasks = $user->getTasks();

        $data = [];

        foreach ($tasks as $task)
        {
            $data[] = $this->taskDTOFactory->getDTOFromTask($task);
        }

        return $data;
    }

    public function getTasksOnCompletedForUserDTO(Security $security, bool $bool): array
    {
        $userId = $this->securityService->getCurrentUserId($security);

        if ($userId === null) {
            return new JsonResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);

        if (!$user)
        {
            return new JsonResponse(['error' => 'User not found']);
        }

        $tasks = $user->getTasks();

        $data = [];

        foreach ($tasks as $task)
        {
            if ($bool === $task->getCompleted())
            {
                $data[] = $this->taskDTOFactory->getDTOFromTask($task);
            }
        }

        return $data;
    }

    public function getAllTasksDTO(): array
    {
        // Use TaskRepository instead ManagerRegistry because it's more specified
        $tasks = $this->repository->findAll();

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
    public function getSingleTaskDTO(int $id): object
    {
        $task = $this->repository->find($id);

        if (!$task)
        {
            throw new TaskNotFoundException($id);
        }

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    public function newTaskDTO(Security $security, Request $request): object
    {
        $userId = $this->securityService->getCurrentUserId($security);

        if ($userId === null) {
            return new JsonResponse('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);

        if (!$user)
        {
            return new JsonResponse(['error' => 'User not found']);
        }

        $task = new Task();
        $task->setDescription($request->get('description'));
        $task->setCompleted($request->get('completed'));

        $user->addTask($task);

        $this->repository->save($task, true);

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    /**
     * @throws TaskNotFoundException
     * @throws \JsonException
     */
    public function editTaskDTO(Request $request, int $id): object
    {
        $task = $this->repository->find($id);

        if (!$task)
        {
            throw new TaskNotFoundException($id);
        }

        $parametr = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $task->setDescription($parametr['description']);
        $task->setCompleted($parametr['completed']);
        $this->repository->save($task, true);

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    /**
     * @throws TaskNotFoundException
     */
    public function deleteTaskDTO(int $id): int
    {
        $task = $this->repository->find($id);

        if (!$task)
        {
            throw new TaskNotFoundException($id);
        }

        $this->repository->remove($task, true);

        return $id;
    }
}