<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    public function __construct(
        private readonly UserService $userService
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

    #[Route('/api/user/new', name: 'user_new', methods: ['POST'])]
    public function registerUser(Request $request): JsonResponse
    {
        try
        {
            $data = $this->userService->newUserDTO($request);

        } catch (\Exception $exception)
        {
            return new JsonResponse(['error' => 'An error occurred: ' . $exception->getMessage()]);
        }

        return new JsonResponse('Created new user successfully with id: ' . $data->id , Response::HTTP_CREATED);

    }
}
