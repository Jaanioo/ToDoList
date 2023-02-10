<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TasksEntity;
use App\Exceptions\TaskNotFoundException;
use App\Repository\TaskRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TasksAPIController extends AbstractController
{
    private $repository;

    public function __construct(TaskRepository $repository) {
        $this->repository = $repository;
    }

    #[Route('/api/task',name: 'task_index', methods: ['GET'])]
    public function getAllTask(): JsonResponse {
        try {

            // Use TaskRepository instead ManagerRegistry because it's more specified
            $tasks = $this->repository->findAll();

            $data = [];

            foreach ($tasks as $task) {
                $data[] = [
                    'id' => $task->getId(),
                    'description' => $task->getDescription(),
                    'completed' => $task->isCompleted(),
                ];
            }

        } catch (\Exception $exception) {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse($data);
    }

    #[Route('/api/task/{id}', name: 'task_show_single', methods: ['GET'])]
    public function getSingleTask(int $id): JsonResponse {
        try {

            $task = $this->repository->find($id);

            if (!$task) {
                throw new TaskNotFoundException($id);
            }

            $data = [
                'id' => $task->getId(),
                'description' => $task->getDescription(),
                'completed' => $task->isCompleted(),
            ];

        } catch (TaskNotFoundException $exception) {
            return new JsonResponse(['An error occurred: ' => $exception->getMessage()], 404);
        }

        return new JsonResponse($data);
    }

    #[Route('/api/task', name: 'task_new', methods: ['POST'])]
    public function newTask(Request $request): JsonResponse {
        try {

            $task = new Task();
            $task->setDescription($request->request->get('description'));
            $task->setCompleted($request->request->get('completed'));

            $this->repository->save($task, true);

        } catch (\Exception $exception) {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse('Created new task successfully with id: ' . $task->getId());

    }

    #[Route('/api/task/{id}/edit', name: 'task_edit', methods: ['PUT', 'PATCH'])]
    public function editTask(Request $request, int $id) {
        try {

            $task = $this->repository->find($id);

            if (!$task) {
                throw new TaskNotFoundException($id);
            }

            $parametr = json_decode($request->getContent(), true);
            $task->setDescription($parametr['description']);
            $task->setCompleted($parametr['completed']);
            $this->repository->save($task, true);

        } catch (TaskNotFoundException $exception) {
            return new JsonResponse(['An error occurred: ' => $exception->getMessage()], 404);
        }

        return new JsonResponse('Edited a task successfully with id: ' . $id);

    }

    #[Route('api/task/{id}/delete', name: 'task_delete', methods: ['DELETE'])]
    public function deleteTask(int $id) {
        try {

            $task = $this->repository->find($id);

            if (!$task) {
                throw new TaskNotFoundException($id);
            }

            $this->repository->remove($task, true);

        } catch (TaskNotFoundException $exception) {
            return new JsonResponse(['An error occurred: ' => $exception->getMessage()], 404);
        }

        return new JsonResponse('Deleted a task successfully with id: ' . $id);

    }
}