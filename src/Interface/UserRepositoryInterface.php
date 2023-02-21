<?php

namespace App\Interface;

use App\Entity\User;

interface UserRepositoryInterface
{
    public function findAll();
    public function find($email, $lockMode, $lockVersion);
    public function save(User $entity, bool $flush );
}