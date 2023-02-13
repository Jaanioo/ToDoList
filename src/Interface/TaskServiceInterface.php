<?php

namespace App\Interface;

use Symfony\Component\HttpFoundation\Request;

interface TaskServiceInterface
{
    public function getAllTaskDTO(): array;
    public function getSingleTaskDTO(int $id): object;
    public function newTaskDTO(Request $request): object;
    public function editTaskDTO(Request $request, int $id):object;
    public function deleteTaskDTO(int $id): int;
}