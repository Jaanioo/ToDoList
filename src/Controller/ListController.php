<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response
    {
        $tasks = [
            ['singleTask' => 'sprzatanie', 'done' => 'false'],
            ['singleTask' => 'gotowanie', 'done' => 'false'],
        ];

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
}