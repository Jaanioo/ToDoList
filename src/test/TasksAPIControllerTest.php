<?php

namespace App\test;

use App\Controller\TasksAPIController;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class TasksAPIControllerTest extends TestCase
{
    protected $tasksAPIController;
    protected $tasksServiceMock;

    protected function setUp(): void
    {
        $this->taskServiceMock = $this->createMock(TaskService::class);
        $this->tasksAPIController = new TasksAPIController($this->taskServiceMock);
    }

    /**
     * @test
     */
    public function getAllTasksReturnsJson()
    {
        $testData = [
            ['id' => 1, 'description' => 'test', 'completed' => true],
            ['id' => 2, 'description' => 'testtest', 'completed' => false]
        ];
        $this->taskServiceMock->method('getAllTasksDTO')->willReturn($testData);

        $response = $this->tasksAPIController->getAllTasks();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertJsonStringEqualsJsonString(json_encode($testData), $response->getContent());
    }
}