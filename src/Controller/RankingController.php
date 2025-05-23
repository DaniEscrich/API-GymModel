<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends AbstractController
{
    #[Route('/api/ranking', name: 'api_ranking', methods: ['GET'])]
    public function getRanking(EntityManagerInterface $em): JsonResponse
    {
        $users = $em->getRepository(User::class)->findAll();
        $resultado = [];

        foreach ($users as $user) {
            $progresses = $user->getProgress()->toArray();
            usort($progresses, fn($a, $b) => $a->getDate() <=> $b->getDate());

            if (count($progresses) === 0) continue;

            $pesoInicial = $progresses[0]->getWeight();
            $pesoActual = $progresses[count($progresses) - 1]->getWeight();
            $pesoPerdido = round($pesoInicial - $pesoActual, 1);

            $createdAt = $user->getCreatedAt();
            $diasDesdeRegistro = $createdAt ? $createdAt->diff(new \DateTimeImmutable())->days : null;

            $resultado[] = [
                'nombreUsuario' => $user->getNombreUsuario(),
                'diasDesdeRegistro' => $diasDesdeRegistro,
                'progresosRegistrados' => count($progresses),
                'pesoInicial' => $pesoInicial,
                'pesoActual' => $pesoActual,
                'pesoPerdido' => $pesoPerdido,
            ];
        }

        // Ordenar por mayor peso perdido
        usort($resultado, fn($a, $b) => $b['pesoPerdido'] <=> $a['pesoPerdido']);

        return new JsonResponse($resultado);
    }

}
