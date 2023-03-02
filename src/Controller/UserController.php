<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $repository
    ) {
    }


    #[Route('/api/user', name: 'users_index', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        try {
            $data = $this->userService->getAllUsersDTO();
        } catch (\Exception $exception) {
            return new JsonResponse('An error occurred: ' . $exception->getMessage());
        }

        return new JsonResponse($data);
    }

    #[Route('/api/register', name: 'user_new', methods: ['POST'])]
    public function registerUser(Request $request, MailerInterface $mailer): JsonResponse
    {
        try {
            $data = $this->userService->newUserDTO($mailer, $request, $this->passwordHasher);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred: ' . $exception->getMessage()]);
        }

        return new JsonResponse('Created new user successfully with id: ' . $data->id, Response::HTTP_CREATED);
    }

    #[Route('/api/login', name: 'api_login')]
    public function loginUser(Request $request, JWTTokenManagerInterface $tokenManager): JsonResponse
    {
        try {
            $token = $this->userService->loginUser($request, $tokenManager);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred: ' . $exception->getMessage()]);
        }

        return new JsonResponse(['token' => $token], Response::HTTP_CREATED);
    }

    #[Route('/api/user/change', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(MailerInterface $mailer, Request $request): JsonResponse
    {
        try {
            $this->userService->changePassword($mailer, $request, $this->passwordHasher);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An error occurred: ' . $exception->getMessage()]);
        }

        return new JsonResponse('Password changed', Response::HTTP_CREATED);
    }
}
