<?php

namespace App\Controller;

use App\Entity\TasksEntity;
use App\Exception\TaskNotFoundException;
use App\Interface\TaskServiceInterface;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksAPIController extends AbstractController
{
    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private TaskServiceInterface $taskService1;

    public function __construct(TaskServiceInterface $taskService1, TaskService $taskService, TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->taskService1 = $taskService1;
        $this->taskService = $taskService;
    }

    #[Route('/api/task',name: 'task_index', methods: ['GET'])]
    public function getAllTask(): JsonResponse
    {
        try
        {
            $data = $this->taskService1->getAllTaskDTO();

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

    #[Route('/api/task', name: 'task_new', methods: ['POST'])]
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