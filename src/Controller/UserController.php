<?php
// src/Controller/UserController.php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    private UserService $userService;
    private SerializerInterface $serializer;

    public function __construct(
        UserService $userService,
        SerializerInterface $serializer
    ) {
        $this->userService = $userService;
        $this->serializer = $serializer;
    }

    // GET all users
    #[Route('', name: 'get_all_users', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        $data = $this->serializer->serialize($users, 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // GET user by ID
    #[Route('/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // POST create user
    #[Route('', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $result = $this->userService->createUser($data);

        if (!empty($result['errors'])) {
            return new JsonResponse(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        $data = $this->serializer->serialize($result['user'], 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    // PUT update user
    #[Route('/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $result = $this->userService->updateUser($user, $data);

        if (!empty($result['errors'])) {
            return new JsonResponse(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST);
        }

        $data = $this->serializer->serialize($result['user'], 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // DELETE user
    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $deleted = $this->userService->deleteUser($user);

        if (!$deleted) {
            return new JsonResponse(['message' => 'User already deleted'], Response::HTTP_GONE);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}