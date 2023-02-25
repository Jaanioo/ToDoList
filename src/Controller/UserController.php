<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{

    public function __construct(
        private readonly UserService $userService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $repository
    ){}


    #[Route('/api/user',name: 'users_index', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        try
        {
            $data = $this->userService->getAllUsersDTO();

        } catch (\Exception $exception)
        {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse($data);
    }

    #[Route('/api/user', name: 'user_new', methods: ['POST'])]
    public function registerUser(Request $request, MailerInterface $mailer): JsonResponse
    {
        try
        {
            $data = $this->userService->newUserDTO($mailer, $request, $this->passwordHasher);

        } catch (\Exception $exception)
        {
            return new JsonResponse(['error' => 'An error occurred: ' . $exception->getMessage()]);
        }

        return new JsonResponse('Created new user successfully with id: ' . $data->id , Response::HTTP_CREATED);

    }

    #[Route('/api/user/login', name: 'api_login', methods: ['GET'])]
    public function loginUser(Request $request,
                              //JWTTokenManagerInterface $tokenManager
    ): JsonResponse
    {
        $credentials = json_decode($request->getContent(), true);

        if (!isset($credentials['username'], $credentials['password']) || !$credentials)
        {
            return new JsonResponse('Missing credentials', Response::HTTP_UNAUTHORIZED);
        }

        $username = $credentials['username'];
        $password = $credentials['password'];

        $user = $this->repository->findOneBy(['email' => $username]);

        //dd($username, $password);

        if (!$user instanceof UserInterface || !$this->passwordHasher->isPasswordValid($user, $password))
        {
            return new JsonResponse('Invalid credentials', Response::HTTP_UNAUTHORIZED);
        }
        dd($this->passwordHasher->isPasswordValid($user, $password));

        //$token = $tokenManager->create($user);

        return new JsonResponse($user->getUserIdentifier());
    }

    #[Route('/api/user/change', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        try
        {
            $this->userService->changePassword($request, $this->passwordHasher);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred: ' . $exception->getMessage()]);
        }

        return new JsonResponse('Password changed', Response::HTTP_CREATED);
    }
}
