<?php

namespace App\Controller;

use App\Entity\TasksEntity;
use App\Repository\TasksEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController
{

    public function __construct(private TasksEntityRepository $tasksEntityRepository)
    {}

    #[Route('/', name: 'app_homepage')]
    public function homepage(EntityManagerInterface $entityManager): Response
    {
        $tasksEntityRepository = $entityManager->getRepository(TasksEntity::class);
        $tasks = $tasksEntityRepository->findAll();

        $tasksDone = [
            ['singleTask' => 'zakupy', 'done' => 'true'],
            ['singleTask' => 'spanie', 'done' => 'true'],
        ];
        dd($tasksDone);

        return $this->render('todolist/homepage.html.twig', [
            'title' => 'ToDoList',
            'tasks' => $tasks,
            'tasksDone' => $tasksDone,
        ]);
    }

    #[Route('/new', name: 'app_list_newtask')]
    public function newTask(EntityManagerInterface $entityManager, Request $request) {

        $task = new TasksEntity();
        $stringText = $request->request->get('newTask');
        $task->setTaskString($stringText);
        $task->setTaskBool(true);

        $entityManager->persist($task);
        $entityManager->flush();

        return $this->redirectToRoute('app_homepage');
    }

    #[Route('/{id}/delete', name: 'app_list_deletetask')]
    public function deleteTask(TasksEntity $task, EntityManagerInterface $entityManager): Response {

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('app_homepage');
    }
}