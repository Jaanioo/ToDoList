<?php

namespace App\Controller\v1;

use App\Exception\TaskNotFoundException;
use App\Service\TaskService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v1/task')]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/user', name: 'tasks_for_user', methods: ['GET'])]
    public function getTasksForUser(Security $security): JsonResponse
    {
        try {
            $data = $this->taskService->getAllTasksForUserDTO($security);
            $this->logger->info('All users tasks displayed successfully');
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while displaying users tasks', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    //There is need to pass int value representing bool ( 0=false 1=true)
    #[Route('/user/{bool}', name: 'task_on_completed', methods: ['GET'])]
    public function getTasksOnCompletedForUser(Security $security, bool $bool): JsonResponse
    {
        try {
            $data = $this->taskService->getTasksOnCompletedForUserDTO($security, $bool);
            $this->logger->info('All tasks displayed successfully', ['completed' => $bool]);
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while displaying tasks', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    //GETTING TASKS WITHOUT USERS
    #[Route('/all', name: 'task_index', methods: ['GET'])]
    public function getAllTasks(): JsonResponse
    {
        try {
            $data = $this->taskService->getAllTasksDTO();
            $this->logger->info('All tasks displayed successfully');
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while displaying all tasks', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'task_show_single', methods: ['GET'])]
    //#[ParamConverter('get', class: Task::class)]
    public function getSingleTask(int $id): JsonResponse
    {
        //return $this->json($id);
        try {
            $data = $this->taskService->getSingleTaskDTO($id);
            $this->logger->info('Task displayed successfully', ['id' => $id]);
        } catch (TaskNotFoundException $exception) {
            $this->logger->error('An error occurred while displaying task', ['id' => $id, 'exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    //NEW, DELETE, EDIT
    #[Route('/new', name: 'task_new', methods: ['POST'])]
    public function newTask(Security $security, Request $request): JsonResponse
    {
        try {
            $this->taskService->newTaskDTO($security, $request);
            $this->logger->info('Task created successfully');
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while adding new task', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            'Created new task successfully! ',
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}/edit', name: 'task_edit', methods: ['PUT', 'PATCH'])]
    public function editTask(Request $request, int $id): JsonResponse
    {
        try {
            $this->taskService->editTaskDTO($request, $id);
            $this->logger->info('Task edited successfully', ['id' => $id]);
        } catch (TaskNotFoundException $exception) {
            $this->logger->error('An error occurred while editing task', ['id' => $id, 'exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            'Edited a task successfully with id: ',
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/delete', name: 'task_delete', methods: ['DELETE'])]
    public function deleteTask(int $id): JsonResponse
    {
        try {
            $this->taskService->deleteTaskDTO($id);
            $this->logger->info('Task deleted successfully', ['id' => $id]);
        } catch (TaskNotFoundException $exception) {
            $this->logger->error('An error occurred while deleting task', ['id' => $id, 'exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            'Deleted a task successfully',
            Response::HTTP_NO_CONTENT
        );
    }
}
