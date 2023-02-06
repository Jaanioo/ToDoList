<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TasksEntity;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksAPIController extends AbstractController
{
    #[Route('/api/task',name: 'task_index', methods: ['GET'])]
    public function getAllTask(ManagerRegistry $registry): Response {
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

        return $this->json($data);
    }

    #[Route('/api/task/{id}', name: 'task_show_single', methods: ['GET'])]
    public function getSingleTask(ManagerRegistry $registry, int $id): Response {
        $task = $registry
            ->getRepository(Task::class)
            ->find($id);

        if (!$task) {
            return $this->json('No task found', 404);
        }

        $data = [
            'id' => $task->getId(),
            'description' => $task->getDescription(),
            'completed' => $task->isCompleted(),
        ];
        return $this->json($data);
    }

    #[Route('/api/task/new', name: 'task_new', methods: ['POST'])]
    public function newTask(ManagerRegistry $registry, Request $request): Response {
        $entityManager = $registry->getManager();

        $task = new Task();
        $task->setDescription($request->request->get('description'));
        $task->setCompleted($request->request->get('completed'));

        $entityManager->persist($task);// prepare symfony to be ready for make sth with data
        $entityManager->flush();//send data

        return $this->json('Created new task successfully with id: ' . $task->getId());
    }

    #[Route('/api/task/{id}/edit', name: 'task_edit', methods: ['PUT'])]
    public function editTask(ManagerRegistry $registry, Request $request, int $id) {
        $entityManager = $registry->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json('No task found', 404);
        }

        $task->setDescription($request->request->get('description'));
        $task->setCompleted($request->request->get('completed'));
        $entityManager->flush();

//        $data = [
//            'id' => $task->getId(),
//            'description' => $task->getDescription(),
//            'completed' => $task->isCompleted(),
//        ];
        return $this->json('Edited a task successfully with id: ' . $id);
    }

    #[Route('api/task/{id}/delete', name: 'task_delete', methods: ['DELETE'])]
    public function deleteTask(ManagerRegistry $registry, int $id) {
        $entityManager = $registry->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json('No task found', 404);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->json('Deleted a task successfully with id: ' . $id);
    }
}