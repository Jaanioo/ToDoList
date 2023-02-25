<?php

namespace App\Service;

use App\Entity\User;
use App\Factory\UserDTOFactory;
use App\Interface\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{

    public function __construct(
        private readonly UserDTOFactory $userDTOFactory,
        private readonly UserRepositoryInterface $repository
    ){}

    public function getAllUsersDTO(): array
    {
        // Use TaskRepository instead ManagerRegistry because it's more specified
        $users = $this->repository->findAll();

        $data = [];

        foreach ($users as $user)
        {
            $data[] = $this->userDTOFactory->getDTOFromUser($user);
        }

        return $data;
    }

    public function getSingleUserDTO(string $email): object
    {
        $user = $this->repository->find($email);

        if (!$user)
        {
            throw new NotFoundHttpException($email);
        }

        return $this->userDTOFactory->getDTOFromUser($user);
    }

    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher): object
    {
        $email = $request->request->get('email');
        $newPasswordPlain = $request->request->get('password');

        $user = $this->repository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new NotFoundHttpException($email);
        }

        $newPasswordHashed = $passwordHasher->hashPassword(
            $user,
            $newPasswordPlain
        );

        $user->setPassword($newPasswordHashed);
        $this->repository->save($user, true);

        return $this->userDTOFactory->getDTOFromUser($user);
    }

    public function newUserDTO(MailerInterface $mailer, Request $request, UserPasswordHasherInterface $passwordHasher): object
    {
        $user = new User();
        $user->setEmail($request->get('email'));
        $user->setUsername($request->get('username'));

        $plainTextPassword = $request->get('password');
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plainTextPassword
        );
        $user->setPassword($hashedPassword);

        $this->repository->save($user, true);

        $email = (new Email())
            ->from('janpalen@example.com')
            ->to($user->getEmail())
            ->subject('Welcome to ToDoList!')
            ->text('Nice to meet you ' . $user->getUsername() . "! ❤️");

        $mailer->send($email);

        return $this->userDTOFactory->getDTOFromUser($user);
    }
}