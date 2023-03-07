<?php

namespace App\Interface;

use App\Entity\Task;

interface TaskRepositoryInterface
{
    public function save(Task $entity, bool $flush);
    public function findAll();
    public function find($id, $lockMode, $lockVersion);
    public function remove(Task $entity, bool $flush);
}
