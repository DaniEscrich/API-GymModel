<?php

namespace App\Controller;

use App\Entity\Progress;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProgressController extends AbstractController
{
    #[Route('/api/progress', name: 'create_progress', methods: ['POST'])]
    public function createProgress(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['user_id'], $data['weight'], $data['date'])) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }

        $user = $em->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $progress = new Progress();
        $progress->setUser($user);
        $progress->setWeight($data['weight']);
        $progress->setDate(new \DateTime($data['date']));

        $em->persist($progress);
        $em->flush();

        return new JsonResponse([
            'id' => $progress->getId(),
            'userId' => $user->getId(),
            'weight' => $progress->getWeight(),
            'date' => $progress->getDate()->format('Y-m-d'),
        ], 201);
    }


    #[Route('/api/progress/{userId}', name: 'get_user_progress', methods: ['GET'])]
    public function getUserProgress(int $userId, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $progressList = $em->getRepository(Progress::class)->findBy(['user' => $user], ['date' => 'ASC']);
        $response = [];

        foreach ($progressList as $progress) {
            $response[] = [
                'id' => $progress->getId(),
                'weight' => $progress->getWeight(),
                'date' => $progress->getDate()->format('Y-m-d'),
            ];
        }

        return new JsonResponse($response);
    }
}
