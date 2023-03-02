<?php

namespace App\Tests\Unit;

use App\Controller\TasksController;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskTest extends TestCase
{
    protected $tasksAPIController;
    protected $tasksServiceMock;

    protected function setUp(): void
    {
        $this->taskServiceMock = $this->createMock(TaskService::class);
        $this->tasksAPIController = new TasksController($this->taskServiceMock);
    }

    public function testGetAllTasksReturnsJson()
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
