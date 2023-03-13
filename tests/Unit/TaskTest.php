<?php

namespace App\Tests\Unit;

use App\Controller\v1\TaskController;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskTest extends TestCase
{
    public function testEmpty(): array
    {
        $stack = [];
        $this->assertEmpty($stack);

        return $stack;
    }

//    protected $tasksAPIController;
//    protected $taskServiceMock;
//
//    protected function setUp(): void
//    {
//        $this->taskServiceMock = $this->createMock(TaskService::class);
//        $this->tasksAPIController = new TaskController($this->taskServiceMock);
//    }
//
//    public function testGetAllTasksReturnsJson()
//    {
//        $testData = [
//            ['id' => 1, 'description' => 'test', 'completed' => true],
//            ['id' => 2, 'description' => 'testtest', 'completed' => false]
//        ];
//        $this->taskServiceMock->method('getAllTasksDTO')->willReturn($testData);
//
//        $response = $this->tasksAPIController->getAllTasks();
//
//        $this->assertInstanceOf(JsonResponse::class, $response);
//        $this->assertJsonStringEqualsJsonString(json_encode($testData), $response->getContent());
//    }
}
