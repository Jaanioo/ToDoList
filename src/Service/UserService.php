<?php

namespace App\Service;

use App\DTO\UserDTO\ChangePasswordUserDTO;
use App\DTO\UserDTO\CreateUserDTO;
use App\DTO\UserDTO\LoginUserDTO;
use App\Entity\User;
use App\Factory\UserDTOFactory;
use App\Interface\UserRepositoryInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService
{
    public function __construct(
        private UserDTOFactory $userDTOFactory,
        private UserRepositoryInterface $repository,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
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
        //dd($errors);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $errorMessages;
        }

        $user = new User();

        if ($this->repository->findOneBy(['email' => $credentials->getEmail()])) {
            return new JsonResponse('Email existed.', Response::HTTP_OK);
        }
        if ($this->repository->findOneBy(['username' => $credentials->getUsername()])) {
            return new JsonResponse(['Username existed'], Response::HTTP_OK);
        }
        $user->setEmail($credentials->getEmail());
        $user->setUsername($credentials->getUsername());

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $credentials->getPassword()
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
        //dd($user);

        if (
            !$user instanceof UserInterface || !$this->passwordHasher->isPasswordValid(
                $user,
                $loginUserDto->getPassword()
            )
        ) {
//            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
            return ['error' => 'Invalid credentials'];
        }

        $token = $tokenManager->create($user);
        $refreshToken = $refreshTokenManager->create();
        $refreshToken->setUsername($user->getUsername());
        $refreshToken->setRefreshToken(bin2hex(random_bytes(16)));
        $validityPeriod = new \DateTime('+31 days');
        $refreshToken->setValid($validityPeriod);
        $refreshTokenManager->save($refreshToken);

        return [
            'token' => $token,
            'refresh_token' => $refreshToken->getRefreshToken()];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function changePassword(
        MailerInterface $mailer,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): object|array {
//        $userEmail = $request->get('email');
//        $newPasswordPlain = $request->get('password');
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

        $email = (new Email())
            ->from('janpalen@example.com')
            ->to($user->getEmail())
            ->subject('Password change in ToDoList!')
            ->text('Your password is changed.  ' . $user->getUsername() . "!");

        $mailer->send($email);

        return $this->userDTOFactory->getDTOFromUser($user);
    }
}
