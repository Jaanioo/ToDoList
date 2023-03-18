<?php

namespace App\Tests\Unit;

use App\Builder\TaskDTOFactory;
use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testCreateTask(): void
    {
        $task = new Task();
        $task->setDescription('abc');
        $task->setCompleted(true);

        $factory = new TaskDTOFactory();
        $dto = $factory->getDTOFromTask($task) ;

        $this->assertSame('abc', $dto->description);
        $this->assertTrue($dto->completed);
    }

    public function testEditTask(): void
    {
        $task = new Task();
        $task->setDescription('abc');
        $task->setCompleted(true);

        $factory = new TaskDTOFactory();
        $dto = $factory->getDTOFromTask($task);

        $this->assertSame('abc', $dto->description);
        $this->assertTrue($dto->completed);

        $task->setDescription('edit');
        $task->setCompleted(false);

        $dto = $factory->getDTOFromTask($task);

        $this->assertSame('edit', $dto->description);
        $this->assertFalse($dto->completed);
    }
}
