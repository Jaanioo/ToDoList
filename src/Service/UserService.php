<?php

namespace App\Service;

use App\DTO\UserDTO\ChangePasswordUserDTO;
use App\DTO\UserDTO\CreateUserDTO;
use App\DTO\UserDTO\LoginUserDTO;
use App\Entity\User;
use App\Builder\UserDTOFactory;
use App\Interface\UserRepositoryInterface;
use Exception;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    ): object|array {
        $credentials = $this->serializer->deserialize($request->getContent(), CreateUserDTO::class, 'json');

        $errors = $this->validator->validate($credentials);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $errorMessages; // tu nie error tylko exception jakies
        }

        if ($this->repository->findOneBy(['email' => $credentials->getEmail()])) {
            return new JsonResponse('Email existed.', Response::HTTP_OK);
        }
        if ($this->repository->findOneBy(['username' => $credentials->getUsername()])) {
            return new JsonResponse(['Username existed'], Response::HTTP_OK);
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
        Request $request,
        JWTTokenManagerInterface $tokenManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ): array {
        $credentials = $this->serializer->deserialize($request->getContent(), LoginUserDTO::class, 'json');
        $loginUserDto = new LoginUserDTO($credentials->getPassword(), $credentials->getUsername());

        $errors = $this->validator->validate($loginUserDto);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return ['error' => $errorMessages];
        }

        $user = $this->repository->findOneBy(['username' => $loginUserDto->getUsername()]);

        if (
            !$user instanceof UserInterface || !$this->passwordHasher->isPasswordValid(
                $user,
                $loginUserDto->getPassword()
            )
        ) {
            return ['error' => 'Invalid credentials'];
        }

        return $this->tokenService->createToken($user);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): object|array {
        $credentials = $this->serializer->deserialize($request->getContent(), ChangePasswordUserDTO::class, 'json');

        $errors = $this->validator->validate($credentials);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return ['error' => $errorMessages];
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
            return ['error' => 'Password is same as previous.'];
        }

        $newPasswordHashed = $passwordHasher->hashPassword(
            $user,
            $credentials->getPassword()
        );

        $user->setPassword($newPasswordHashed);
        $this->repository->save($user, true);

        $this->mailer->sendChangingPasswEmail($user);

        return $this->userDTOFactory->getDTOFromUser($user);
    }
}
