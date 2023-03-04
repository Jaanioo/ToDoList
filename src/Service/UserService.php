<?php

namespace App\Service;

use App\Entity\User;
use App\Factory\UserDTOFactory;
use App\Interface\UserRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Response;

class UserService
{
    public function __construct(
        private readonly UserDTOFactory $userDTOFactory,
        private readonly UserRepositoryInterface $repository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function getAllUsersDTO(): array
    {
        // Use TaskRepository instead ManagerRegistry because it's more specified
        $users = $this->repository->findAll();

        $data = [];

        foreach ($users as $user) {
            $data[] = $this->userDTOFactory->getDTOFromUser($user);
        }

        return $data;
    }

    public function getSingleUserDTO(string $email): object
    {
        $user = $this->repository->find($email);

        if (!$user) {
            throw new NotFoundHttpException($email);
        }

        return $this->userDTOFactory->getDTOFromUser($user);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function newUserDTO(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): object {
        $userEmail = $request->get('email');
        $plainTextPassword = $request->get('password');

        //Validation email format
        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        // Validate password length
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', $plainTextPassword)) {
            throw new \InvalidArgumentException('Invalid password format.');
        }

        $user = new User();
        $user->setEmail($userEmail);
        $user->setUsername($request->get('username'));

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plainTextPassword
        );
        $user->setPassword($hashedPassword);

        $email = (new Email())
            ->from($_ENV['FROM_EMAIL'])
            ->to($user->getEmail())
            ->subject('Welcome to ToDoList!')
            ->text('Nice to meet you ' . $user->getUsername() . "! ❤️");

        $this->mailer->send($email);

        $this->repository->save($user, true);

        return $this->userDTOFactory->getDTOFromUser($user);
    }

    public function loginUser(Request $request, JWTTokenManagerInterface $tokenManager): string
    {
        $credentials = json_decode($request->getContent(), true);

        if (!isset($credentials['username'], $credentials['password']) || !$credentials) {
            return new JsonResponse('Missing credentials', Response::HTTP_UNAUTHORIZED);
        }

        $username = $credentials['username'];
        $password = $credentials['password'];
        //dd($username, $password);

        $user = $this->repository->findOneBy(['username' => $username]);
        //dd($user);

        if (!$user instanceof UserInterface || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse('Invalid credentials', Response::HTTP_UNAUTHORIZED);
        }

        return $tokenManager->create($user);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function changePassword(
        MailerInterface $mailer,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): object {
        $userEmail = $request->get('email');
        $newPasswordPlain = $request->get('password');

        //Validation email format
        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        // Validate password length
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', $newPasswordPlain)) {
            throw new \InvalidArgumentException('Invalid password format.');
        }

        $user = $this->repository->findOneBy(['email' => $userEmail]);

        if (!$user) {
            throw new NotFoundHttpException($userEmail);
        }

        $newPasswordHashed = $passwordHasher->hashPassword(
            $user,
            $newPasswordPlain
        );

        $user->setPassword($newPasswordHashed);
        $this->repository->save($user, true);

        $email = (new Email())
            ->from('janpalen@example.com')
            ->to($user->getEmail())
            ->subject('Password change in ToDoList!')
            ->text('Your password is changed.  ' . $user->getUsername() . "!");

        $mailer->send($email);

        return $this->userDTOFactory->getDTOFromUser($user);
    }
}
