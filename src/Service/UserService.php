<?php

namespace App\Service;

use App\DTO\UserDTO\ChangePasswordUserDTO;
use App\DTO\UserDTO\CreateUserDTO;
use App\DTO\UserDTO\LoginUserDTO;
use App\DTO\UserDTO\UserDTO;
use App\Entity\User;
use App\Builder\UserDTOFactory;
use App\Exception\ValidationException;
use App\Interface\UserRepositoryInterface;
use Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    public function __construct(
        private readonly UserDTOFactory $userDTOFactory,
        private readonly UserRepositoryInterface $repository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly MailerService $mailer,
        private readonly TokenService $tokenService
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

    public function getSingleUserDTO(string $email): UserDTO
    {
        $user = $this->repository->find($email);

        if (!$user) {
            throw new NotFoundHttpException($email);
        }

        return $this->userDTOFactory->getDTOFromUser($user);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ValidationException
     */
    public function newUserDTO(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): UserDTO {
        $credentials = $this->serializer->deserialize($request->getContent(), CreateUserDTO::class, 'json');

        $errors = $this->validator->validate($credentials);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new ValidationException($errorMessages);
        }

        if ($this->repository->findOneBy(['email' => $credentials->getEmail()])) {
            throw new CustomUserMessageAuthenticationException('Email existed');
        }
        if ($this->repository->findOneBy(['username' => $credentials->getUsername()])) {
            throw new CustomUserMessageAuthenticationException('Username existed');
        }

        $user = new User();
        $user->setEmail($credentials->getEmail());
        $user->setUsername($credentials->getUsername());

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $credentials->getPassword()
        );
        $user->setPassword($hashedPassword);

        $this->repository->save($user, true);

        $this->mailer->sendWelcomeEmail($user);

        return $this->userDTOFactory->getDTOFromUser($user);
    }

    /**
     * @throws Exception
     */
    public function loginUser(
        Request $request
    ): array {
        $credentials = $this->serializer->deserialize($request->getContent(), LoginUserDTO::class, 'json');
        $loginUserDto = new LoginUserDTO($credentials->getPassword(), $credentials->getUsername());

        $errors = $this->validator->validate($loginUserDto);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            throw new ValidationException($errorMessages);
        }

        $user = $this->repository->findOneBy(['username' => $loginUserDto->getUsername()]);

        if (
            !$user instanceof UserInterface || !$this->passwordHasher->isPasswordValid(
                $user,
                $loginUserDto->getPassword()
            )
        ) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials');
        }

        return $this->tokenService->createToken($user);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ValidationException
     */
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): UserDTO {
        $credentials = $this->serializer->deserialize($request->getContent(), ChangePasswordUserDTO::class, 'json');

        $errors = $this->validator->validate($credentials);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            throw new ValidationException($errorMessages);
        }

        $user = $this->repository->findOneBy(['email' => $credentials->getEmail()]);

        if (!$user) {
            throw new NotFoundHttpException($credentials->getEmail());
        }

        if (
            $this->passwordHasher->isPasswordValid(
                $user,
                $credentials->getPassword()
            )
        ) {
            throw new CustomUserMessageAuthenticationException('Password is same as previous');
        }

        $newPasswordHashed = $passwordHasher->hashPassword(
            $user,
            $credentials->getPassword()
        );

        $user->setPassword($newPasswordHashed);
        $this->repository->save($user, true);

        $this->mailer->sendChangingPasswordEmail($user);

        return $this->userDTOFactory->getDTOFromUser($user);
    }
}
