<?php
// src/Service/UserService.php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Get all active users
     *
     * @return User[]
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->findActiveUsers();
    }

    /**
     * Find user by ID
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Check if email is already in use
     */
    public function isEmailInUse(string $email, ?int $excludeUserId = null): bool
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);

        if (!$existingUser) {
            return false;
        }

        if ($excludeUserId !== null && $existingUser->getId() === $excludeUserId) {
            return false;
        }

        return true;
    }

    /**
     * Create a new user
     *
     * @param array $data User data
     * @return array{user: ?User, errors: array}
     */
    public function createUser(array $data): array
    {
        // Check if email already exists
        if ($this->isEmailInUse($data['email'] ?? '')) {
            return ['user' => null, 'errors' => ['email' => 'Email is already in use']];
        }

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setFirstName($data['firstName'] ?? '');
        $user->setLastName($data['lastName'] ?? '');
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        // Hash the password
        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        // Validate the user entity
        $validationErrors = $this->validateUser($user);
        if (!empty($validationErrors)) {
            return ['user' => null, 'errors' => $validationErrors];
        }

        // Save to database
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return ['user' => $user, 'errors' => []];
    }

    /**
     * Update an existing user
     *
     * @param User $user User to update
     * @param array $data Updated user data
     * @return array{user: ?User, errors: array}
     */
    public function updateUser(User $user, array $data): array
    {
        if (isset($data['email'])) {
            // Check if another user already has this email
            if ($this->isEmailInUse($data['email'], $user->getId())) {
                return ['user' => null, 'errors' => ['email' => 'This email is already taken.']];
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }

        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        // Validate the user entity
        $validationErrors = $this->validateUser($user);
        if (!empty($validationErrors)) {
            return ['user' => null, 'errors' => $validationErrors];
        }

        $this->entityManager->flush();

        return ['user' => $user, 'errors' => []];
    }

    /**
     * Soft delete a user
     *
     * @return bool True if deleted, false if already deleted
     */
    public function deleteUser(User $user): bool
    {
        if ($user->isDeleted()) {
            return false;
        }

        $this->userRepository->softDeleteUser($user);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Validate a user entity
     *
     * @return array Validation errors
     */
    private function validateUser(User $user): array
    {
        $errors = $this->validator->validate($user);

        if (count($errors) === 0) {
            return [];
        }

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }

        return $errorMessages;
    }
}