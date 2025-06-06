<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatCoachController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/api/chat-coach', name: 'chat_coach', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $mensajeUsuario = $data['mensaje'] ?? '';

        if (empty($mensajeUsuario)) {
            return new JsonResponse(['error' => 'Mensaje vacío'], 400);
        }

        $promptSistema = <<<PROMPT
Eres un asistente de gimnasio. Solo puedes responder sobre temas del gimnasio. 
Responde usando siempre el mismo formato. 
Ejemplos:
- Horarios: "Lunes - Viernes de 9:00 a 22:00. Sábado y Domingo cerrados."
- Ubicación: "Puedes encontrar la ubicación en la pantalla principal."
- Apuntarse a clases: "En la pantalla 'Clases'."
- Ranking: "En la pantalla 'Ranking'."
- Contacto: "En el apartado 'Contacto' del menú."
- Cambiar imagen o contraseña: "En la sección 'Perfil'."
- Ver progreso: "En la pantalla 'Progreso'."
Si te preguntan algo no relacionado, responde: "Lo siento, solo puedo ayudarte con temas del gimnasio."
Si te piden un plan personalizado, responde: "Para planes personalizados, visita la pantalla Planes IA."
"Si te preguntan cualquier cosa relacionada con los entrenadores di que actualmente los entrenadores no están disponibles."
"Si te pregutan cualquier cosa relacionada con el gimnasio puedes contestar en plan como se hace este ejercicio de biceps y demás y para quedar bien, explicalo y encima ponle un enlace de youtube con un video, intenta darme el video en plan Aquí tienes un video de ejemplo: enlace, pues se lo puedes explicar o cosas asi"
"Si te preguntan y que cosas puedo preguntarte, le dices pues los horarios la ubicacio ranking apuntarse a clases etcc todo lo que te dije anterioremnte"
"Si te dicen gracias o Hola o algo asi parecido, pues encantado de ayudarte o hola soy la IA de GymModel que necesitas, cosas asi"
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => $promptSistema],
                        ['role' => 'user', 'content' => $mensajeUsuario],
                    ],
                    'temperature' => 0.4,
                ],
            ]);

            $result = $response->toArray();
            $content = $result['choices'][0]['message']['content'] ?? 'No se pudo generar respuesta.';

            return new JsonResponse(['respuesta' => $content]);
        } catch (\Exception $e) {
            return new JsonResponse(['respuesta' => 'Lo siento, ha ocurrido un error. Inténtalo de nuevo.']);
        }
    }
}
