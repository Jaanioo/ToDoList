<?php

namespace App\Service;

use App\DTO\TaskDTO\CreateTaskDTO;
use App\DTO\TaskDTO\TaskDTO;
use App\Entity\Task;
use App\Exception\TaskNotFoundException;
use App\Builder\TaskDTOFactory;
use App\Interface\TaskRepositoryInterface;
use App\Interface\UserRepositoryInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class TaskService
{
    public function __construct(
        private readonly TaskDTOFactory $taskDTOFactory,
        private readonly TaskRepositoryInterface $repository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly SecurityService $securityService,
        private readonly SerializerInterface $serializer
    ) {
    }

//    public function getAllTasksForUserDTO(Security $security): array
//    {
////        //DOKONCZ TO ZEBY DAWALO USERA NIE SECURITY??????????
//        $userId = $this->securityService->getCurrentUserId($security);
//
//        if ($userId === null) {
//            throw new UnauthorizedHttpException('Unauthorized', Response::HTTP_UNAUTHORIZED);
//        }
//
//        $user = $this->userRepository->find($userId);
//
//        if (!$user) {
//            throw new NotFoundHttpException('User not found');
//        }
//
//        $tasks = $user->getTasks();
//
//        $data = [];
//
//        foreach ($tasks as $task) {
//            $data[] = $this->taskDTOFactory->getDTOFromTask($task);
//        }
//
//        return $data;
//    }
    public function getAllTasksForUserDTO(): array
    {
//        //DOKONCZ TO ZEBY DAWALO USERA NIE SECURITY??????????
        $currentUser = $this->securityService->getCurrentUserId();

        $user = $this->userRepository->find($currentUser->getId());

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $tasks = $user->getTasks();

        $data = [];

        foreach ($tasks as $task) {
            $data[] = $this->taskDTOFactory->getDTOFromTask($task);
        }

        return $data;
    }

    public function getTasksOnCompletedForUserDTO(Security $security, bool $bool): array
    {
        $userId = $this->securityService->getCurrentUserId($security);

        if ($userId === null) {
            throw new UnauthorizedHttpException('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $tasks = $user->getTasks();

        $data = [];

        foreach ($tasks as $task) {
            if ($bool === $task->getCompleted()) {
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

        foreach ($tasks as $task) {
            $data[] = $this->taskDTOFactory->getDTOFromTask($task);
        }

        return $data;
    }

    /**
     * @throws TaskNotFoundException
     */
    public function getSingleTaskDTO(int $id): TaskDTO
    {
        $task = $this->repository->find($id);

        if (!$task) {
            throw new TaskNotFoundException($id);
        }

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    /**
     * @throws \Exception
     */
    public function newTaskDTO(Security $security, Request $request): TaskDTO
    {
        $userId = $this->securityService->getCurrentUserId($security);

        if ($userId === null) {
            throw new UnauthorizedHttpException('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $data = $this->serializer->deserialize($request->getContent(), CreateTaskDTO::class, 'json');

        $task = new Task();
        $task->setDescription($data->getDescription());
        $task->setCompleted($data->isCompleted());

        $user->addTask($task);

        $this->repository->save($task, true);

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    /**
     * @throws TaskNotFoundException
     * @throws \JsonException
     */
    public function editTaskDTO(Request $request, int $id): TaskDTO
    {
        $task = $this->repository->find($id);

        if (!$task) {
            throw new TaskNotFoundException($id);
        }

        $data = $this->serializer->deserialize($request->getContent(), CreateTaskDTO::class, 'json');
        $task->setDescription($data['description']);
        $task->setCompleted($data['completed']);
        $this->repository->save($task, true);

        return $this->taskDTOFactory->getDTOFromTask($task);
    }

    /**
     * @throws TaskNotFoundException
     */
    public function deleteTaskDTO(int $id): int
    {
        $task = $this->repository->find($id);

        if (!$task) {
            throw new TaskNotFoundException($id);
        }

        $this->repository->remove($task, true);

        return $id;
    }
}
