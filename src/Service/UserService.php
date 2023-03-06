<?php

namespace App\Service;

use App\DTO\CreateUserDTO;
use App\DTO\LoginUserDTO;
use App\Entity\User;
use App\Factory\UserDTOFactory;
use App\Interface\UserRepositoryInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    public function __construct(
        private readonly UserDTOFactory $userDTOFactory,
        private readonly UserRepositoryInterface $repository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailerInterface $mailer,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
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
        $data = $this->serializer->deserialize($request->getContent(), CreateUserDTO::class, 'json');

        $errors = $this->validator->validate($data);
        //dd($errors);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $errorMessages;
        }

        $user = new User();
        $user->setEmail($data->getEmail());
        $user->setUsername($data->getUsername());

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data->getPassword()
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
