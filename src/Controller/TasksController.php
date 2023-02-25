<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\TaskNotFoundException;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{

    public function __construct(private readonly TaskService $taskService)
    {
    }

    #[Route('/api/task',name: 'task_index', methods: ['GET'])]
    public function getAllTasks(): JsonResponse
    {
        try
        {
            $data = $this->taskService->getAllTasksDTO();

        } catch (\Exception $exception)
        {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse($data);
    }

    #[Route('/api/task/{id}', name: 'task_show_single', methods: ['GET'])]
    public function getSingleTask(int $id): JsonResponse
    {
        try
        {
            $data = $this->taskService->getSingleTaskDTO($id);

        } catch (TaskNotFoundException $exception)
        {
            return new JsonResponse(['An error occurred: ' => $exception->getMessage()], 404);
        }

        return new JsonResponse($data);
    }

    #[Route('/api/task/user/{user_id}', name: 'tasks_for_user', methods: ['GET'])]
    public function getTasksForUser(int $user_id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($user_id);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $tasks = $user->getTasks();

        $data = [];
        foreach ($tasks as $task) {
            $data[] = [
                'id' => $task->getId(),
                'description' => $task->getDescription(),
                'completed' => $task->getCompleted(),
                'created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/task/new', name: 'task_new', methods: ['POST'])]
    public function newTask(Request $request): JsonResponse
    {
        try
        {
            $data = $this->taskService->newTaskDTO($request);

        } catch (\Exception $exception)
        {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse('Created new task successfully with id: ' . $data->id , Response::HTTP_CREATED);

    }

    #[Route('/api/task/{id}/edit', name: 'task_edit', methods: ['PUT', 'PATCH'])]
    public function editTask(Request $request, int $id): JsonResponse
    {
        try
        {
            $data = $this->taskService->editTaskDTO($request, $id);

        } catch (TaskNotFoundException $exception)
        {
            return new JsonResponse(['An error occurred: ' => $exception->getMessage()], 404);
        }

        return new JsonResponse('Edited a task successfully with id: ' . $data->id);

    }

    #[Route('api/task/{id}/delete', name: 'task_delete', methods: ['DELETE'])]
    public function deleteTask(int $id): JsonResponse
    {
        try
        {
            $this->taskService->deleteTaskDTO($id);

        } catch (TaskNotFoundException $exception)
        {
            return new JsonResponse(['An error occurred: ' => $exception->getMessage()], 404);
        }

        return new JsonResponse('Deleted a task successfully with id: ' . $id);

    }
}