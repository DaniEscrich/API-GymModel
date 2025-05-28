<?php

namespace App\Controller;

use App\Entity\IAPlanFavorito;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class IAFavoritoController extends AbstractController
{
    #[Route('/api/ia/favorito', name: 'guardar_favorito', methods: ['POST'])]
    public function guardarFavorito(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $userId = $data['userId'] ?? null;
        $contenido = $data['contenido'] ?? null;

        if (!$userId || !$contenido) {
            return new JsonResponse(['error' => 'Faltan campos obligatorios'], 400);
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        $favorito = new IAPlanFavorito();
        $favorito->setUser($user);
        $favorito->setContenido($contenido);
        $favorito->setFechaGuardado(new \DateTimeImmutable());

        $em->persist($favorito);
        $em->flush();

        return new JsonResponse(['success' => true, 'id' => $favorito->getId()], 201);
    }
    #[Route('/api/ia/favoritos/{id}', name: 'obtener_favoritos', methods: ['GET'])]
    public function obtenerFavoritos(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        $favoritos = $em->getRepository(IAPlanFavorito::class)->findBy(['user' => $user]);

        $data = [];
        foreach ($favoritos as $favorito) {
            $data[] = [
                'id' => $favorito->getId(),
                'contenido' => $favorito->getContenido(),
                'fechaGuardado' => $favorito->getFechaGuardado()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data, 200);
    }

    #[Route('/api/ia/favorito/{id}', name: 'eliminar_favorito', methods: ['DELETE'])]
    public function eliminarFavorito(int $id, EntityManagerInterface $em): JsonResponse
    {
        $favorito = $em->getRepository(IAPlanFavorito::class)->find($id);

        if (!$favorito) {
            return new JsonResponse(['error' => 'Favorito no encontrado'], 404);
        }

        $em->remove($favorito);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }


}
