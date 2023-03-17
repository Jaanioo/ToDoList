<?php

namespace App\Controller\API\v1;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController
{
    public function __construct(TaskRepository $taskRepository)
    {
    }

    #[Route('/', name: 'app_homepage')]
    public function homepage(EntityManagerInterface $entityManager): Response
    {
        $taskRepository = $entityManager->getRepository(Task::class);
        $tasks = $taskRepository->findAll();

        $tasksDone = [
            ['singleTask' => 'zakupy', 'done' => 'true'],
            ['singleTask' => 'spanie', 'done' => 'true'],
        ];

        return $this->render('todolist/homepage.html.twig', [
            'title' => 'ToDoList',
            'tasks' => $tasks,
            'tasksDone' => $tasksDone,
            ]);
    }

    #[Route('/new', name: 'app_list_newtask')]
    public function newTask(EntityManagerInterface $entityManager, Request $request): Response
    {

        $task = new Task();
        $stringText = $request->request->get('newTask');
        $task->setDescription($stringText);
        $task->setCompleted(true);

        $entityManager->persist($task);
        $entityManager->flush();

        return $this->redirectToRoute('app_homepage');
    }

    #[Route('/{id}/delete', name: 'app_list_deletetask')]
    public function deleteTask(Task $task, EntityManagerInterface $entityManager): Response
    {

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('app_homepage');
    }
}
