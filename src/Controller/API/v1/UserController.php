<?php

namespace App\Controller\API\v1;

use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v1/user')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger
    ) {
    }


    #[Route('/all', name: 'users_index', methods: ['GET'])]
    public function displayAllUsers(): JsonResponse
    {
        try {
            $data = $this->userService->getAllUsersDTO();
            $this->logger->info('Users displayed successfully');
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while displaying users', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $data,
            Response::HTTP_OK
        );
    }

    #[Route('/register', name: 'user_new', methods: ['POST'])]
    public function registerNewUser(Request $request): JsonResponse
    {
        try {
            $this->userService->newUserDTO($request, $this->passwordHasher);
            $this->logger->info('User registered successfully');
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while registered', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
            return $this->json(
                'Created new user successfully.',
                Response::HTTP_CREATED
            );
    }

    #[Route('/login', name: 'api_login')]
    public function loginUser(
        Request $request
    ): JsonResponse {
        try {
            $data = $this->userService->loginUser($request);

            if (isset($data['error'])) {
                return $this->json(
                    ['error' => $data['error']],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $token = $data['token'];
            $refreshToken = $data['refresh_token'];
            $cookie = $data['cookie'];
            $this->logger->info('User logged in successfully');
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while logging in', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->json(
            ['token' => $token,
                'refresh_token' => $refreshToken,
                'cookie' => $cookie],
            Response::HTTP_OK
        );
    }

    #[Route('/change', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotUserPassword(Request $request): JsonResponse
    {
        try {
            $this->userService->changePassword($request, $this->passwordHasher);
            $this->logger->info('Password changed successfully');
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while changing password', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            'Password changed',
            Response::HTTP_OK
        );
    }

    #[Route('/logout', name: 'api_logout')]
    public function logoutUser(): Response
    {
        return new Response('Logout', Response::HTTP_NO_CONTENT);
    }
}
