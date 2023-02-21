<?php

namespace App\Service;

use App\Entity\User;
use App\Factory\UserDTOFactory;
use App\Interface\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

class UserService
{

    public function __construct(
        private readonly UserDTOFactory $userDTOFactory,
        private readonly UserRepositoryInterface $repository
    ){}

    public function getAllUsersDTO(): array
    {
        // Use TaskRepository instead ManagerRegistry because it's more specified
        $tasks = $this->repository->findAll();

        $data = [];

        foreach ($tasks as $task)
        {
            $data[] = $this->userDTOFactory->getDTOFromUser($task);
        }

        return $data;
    }

    public function newUserDTO(Request $request): object
    {
        $user = new User();
        $user->setEmail($request->get('email'));
        $user->setUsername($request->get('username'));
        $user->setPassword($request->get('password'));

        $this->repository->save($user, true);

        return $this->userDTOFactory->getDTOFromUser($user);
    }
}