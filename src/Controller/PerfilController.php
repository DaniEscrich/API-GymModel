<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PerfilController extends AbstractController
{
    #[Route('/api/perfil/actualizar', name: 'actualizar_perfil', methods: ['POST'])]
    public function actualizarPerfil(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $id = $data['id'] ?? null;
        $nuevaPassword = $data['nuevaPassword'] ?? null;
        $imagenBase64 = $data['imagenBase64'] ?? null;

        if (!$id) {
            return new JsonResponse(['error' => 'Falta el ID del usuario'], 400);
        }

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        if ($nuevaPassword) {
            $user->setPassword(password_hash($nuevaPassword, PASSWORD_BCRYPT));
        }

        if ($imagenBase64) {
            $imageData = base64_decode($imagenBase64);
            $path = __DIR__.'/../../public/uploads/perfiles/user_'.$user->getId().'.jpg';
            file_put_contents($path, $imageData);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/perfil/imagen/{id}', name: 'obtener_imagen_perfil', methods: ['GET'])]
    public function obtenerImagenPerfil(int $id): JsonResponse
    {
        $path = __DIR__.'/../../public/uploads/perfiles/user_'.$id.'.jpg';

        if (!file_exists($path)) {
            return new JsonResponse(['error' => 'Imagen no encontrada'], 404);
        }

        $image = file_get_contents($path);
        $base64 = base64_encode($image);

        return new JsonResponse(['imagenBase64' => $base64]);
    }

    #[Route('/api/perfil/datos/{id}', name: 'obtener_datos_perfil', methods: ['GET'])]
    public function obtenerDatosPerfil(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        return new JsonResponse([
            'correoElectronico' => $user->getCorreoElectronico()
        ]);
    }
}
