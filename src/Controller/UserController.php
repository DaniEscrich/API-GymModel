<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nombreUsuario']) || !isset($data['password']) || !isset($data['correoElectronico'])) {
            return $this->json(['error' => 'Faltan campos obligatorios'], 400);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['nombreUsuario' => $data['nombreUsuario']]);
        if ($existingUser) {
            return $this->json(['error' => 'El nombre de usuario ya está en uso'], 400);
        }

        $existingEmail = $em->getRepository(User::class)->findOneBy(['correoElectronico' => $data['correoElectronico']]);
        if ($existingEmail) {
            return $this->json(['error' => 'El correo electrónico ya está en uso'], 400);
        }

        $user = new User();
        $user->setNombreUsuario($data['nombreUsuario']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setCorreoElectronico($data['correoElectronico']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $this->json([
            'id' => $user->getId(),
            'nombreUsuario' => $user->getNombreUsuario(),
            'correoElectronico' => $user->getCorreoElectronico(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nombreUsuario']) || !isset($data['password'])) {
            return $this->json(['error' => 'Faltan campos obligatorios'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['nombreUsuario' => $data['nombreUsuario']]);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return $this->json(['error' => 'Credenciales inválidas'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'nombreUsuario' => $user->getNombreUsuario(),
            'correoElectronico' => $user->getCorreoElectronico(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }
    #[Route('/api/perfil/imagen/{id}', name: 'actualizar_imagen_perfil', methods: ['POST'])]
    public function actualizarImagenPerfil(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['imagenBase64'])) {
            return $this->json(['error' => 'Falta la imagen'], 400);
        }

        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        $user->setImagenBase64($data['imagenBase64']);
        $em->flush();

        return $this->json(['message' => 'Imagen actualizada correctamente']);
    }

}
