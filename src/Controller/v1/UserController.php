<?php

namespace App\Controller\v1;

use App\Entity\RefreshToken;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Doctrine\RefreshTokenRepositoryInterface;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v1/user')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger,
        private readonly UserRepository $repository
    ) {
    }


    #[Route('/all', name: 'users_index', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
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
    public function registerUser(Request $request): JsonResponse
    {
        try {
            $data = $this->userService->newUserDTO($request, $this->passwordHasher);
            $this->logger->info('User registered successfully');
        } catch (\Exception $exception) {
            $this->logger->error('An error occurred while registered', ['exception' => $exception]);
            return $this->json(
                ['An error occurred: ' => $exception->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json(
            'Created new user successfully with id: ' . $data->id,
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'api_login')]
    public function loginUser(
        Request $request,
        JWTTokenManagerInterface $tokenManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ): JsonResponse {
        try {
            $data = $this->userService->loginUser($request, $tokenManager, $refreshTokenManager);
            $token = $data['token'];
            $refreshToken = $data['refresh_token'];
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
                'refresh_token' => $refreshToken],
            Response::HTTP_OK
        );
    }

    #[Route('/change', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(MailerInterface $mailer, Request $request): JsonResponse
    {
        try {
            $this->userService->changePassword($mailer, $request, $this->passwordHasher);
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
}
