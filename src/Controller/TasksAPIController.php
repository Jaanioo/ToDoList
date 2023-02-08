<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TasksEntity;
use App\Exceptions\TaskNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TasksAPIController extends AbstractController
{
    #[Route('/api/task',name: 'task_index', methods: ['GET'])]
    public function getAllTask(ManagerRegistry $registry): JsonResponse {
        try {

            $tasks = $registry
                ->getRepository(Task::class)
                ->findAll();

            $data = [];

            foreach ($tasks as $task) {
                $data[] = [
                    'id' => $task->getId(),
                    'description' => $task->getDescription(),
                    'completed' => $task->isCompleted(),
                ];
            }

            //return $this->json($data);

        } catch (\Exception $exception) {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse($data);

    }

    #[Route('/api/task/{id}', name: 'task_show_single', methods: ['GET'])]
    public function getSingleTask(ManagerRegistry $registry, int $id): JsonResponse {
        try {

            $task = $registry
                ->getRepository(Task::class)
                ->find($id);

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

    #[Route('/api/task/new', name: 'task_new', methods: ['POST'])]
    public function newTask(ManagerRegistry $registry, Request $request): JsonResponse {
        try {

            $entityManager = $registry->getManager();

            $task = new Task();
            $task->setDescription($request->request->get('description'));
            $task->setCompleted($request->request->get('completed'));

            $entityManager->persist($task);// prepare symfony to be ready for make sth with data
            $entityManager->flush();//send data

            //return $this->json('Created new task successfully with id: ' . $task->getId());

        } catch (\Exception $exception) {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse('Created new task successfully with id: ' . $task->getId());

    }

    #[Route('/api/task/{id}/edit', name: 'task_edit', methods: ['PUT', 'PATCH'])]
    public function editTask(ManagerRegistry $registry, Request $request, int $id) {
        try {

            $entityManager = $registry->getManager();
            $task = $entityManager->getRepository(Task::class)->findOneBy(['id' => $id]);

            if (!$task) {
                throw new TaskNotFoundException($id);
            }

            $parametr = json_decode($request->getContent(), true);
            $task->setDescription($parametr['description']);
            $task->setCompleted($parametr['completed']);
            $entityManager->flush();

            //return $this->json('Edited a task successfully with id: ' . $id);

        } catch (TaskNotFoundException $exception) {
            return new JsonResponse(['An error occurred: ' => $exception->getMessage()], 404);
        }

        return new JsonResponse('Edited a task successfully with id: ' . $id);

    }

    #[Route('api/task/{id}/delete', name: 'task_delete', methods: ['DELETE'])]
    public function deleteTask(ManagerRegistry $registry, int $id) {
        try {

            $entityManager = $registry->getManager();
            $task = $entityManager->getRepository(Task::class)->find($id);

            if (!$task) {
                throw new TaskNotFoundException($id);
            }

            $entityManager->remove($task);
            $entityManager->flush();

            //return $this->json('Deleted a task successfully with id: ' . $id);

        } catch (TaskNotFoundException $exception) {
            return new JsonResponse(['An error occurred: ' => $exception->getMessage()], 404);
        }

        return new JsonResponse('Deleted a task successfully with id: ' . $id);

    }
}