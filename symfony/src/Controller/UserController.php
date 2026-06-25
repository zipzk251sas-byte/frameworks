<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class UserController
{
    #[Route('', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $users = $em->getRepository(User::class)->findAll();
        return new JsonResponse(array_map(fn(User $user) => $user->toArray(), $users));
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse($user->toArray());
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['name']) || empty($data['email']) || !isset($data['age'])) {
            return new JsonResponse(['message' => 'Fields name, email and age are required'], 422);
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setAge((int) $data['age']);

        $em->persist($user);
        $em->flush();

        return new JsonResponse($user->toArray(), 201);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['age'])) {
            $user->setAge((int) $data['age']);
        }

        $user->setUpdatedAt();
        $em->flush();

        return new JsonResponse($user->toArray());
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'User deleted']);
    }
}
