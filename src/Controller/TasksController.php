<?php

namespace App\Controller;

use App\Exception\TaskNotFoundException;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{

    public function __construct(private readonly TaskService $taskService) {}

    #[Route('/api/task/user/{userId}', name: 'tasks_for_user', methods: ['GET'])]
    public function getTasksForUser(int $userId): JsonResponse
    {
        try
        {
            $data = $this->taskService->getAllTasksForUserDTO($userId);
        } catch (\Exception $exception)
        {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse($data);
    }

    //There is need to pass int value representing bool ( 0=false 1=true)
    #[Route('/api/task/user/{userId}/{bool}', name: 'task_on_completed', methods: ['GET'])]
    public function getTasksOnCompletedForUser(int $userId, bool $bool): JsonResponse
    {
        try
        {
            $data = $this->taskService->getTasksOnCompletedForUserDTO($userId, $bool);
        } catch (\Exception $exception)
        {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse($data);
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

    #[Route('/api/task/new/{userId}', name: 'task_new', methods: ['POST'])]
    public function newTask(Request $request, int $userId): JsonResponse
    {
        try
        {
            $data = $this->taskService->newTaskDTO($request, $userId);

        } catch (\Exception $exception)
        {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse('Created new task successfully! ', Response::HTTP_CREATED);

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