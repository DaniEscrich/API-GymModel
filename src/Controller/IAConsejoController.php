<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IAConsejoController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/api/consejo', name: 'api_consejo', methods: ['POST'])]
    public function generarConsejo(Request $request): JsonResponse
    {
        $prompt = <<<PROMPT
Eres un coach motivacional y experto en salud física y mental. Dame UN CONSEJO breve y motivador para comenzar el día, dirigido a personas que entrenan en el gimnasio.

Ejemplo de respuesta: "Recuerda que cada repetición cuenta. ¡Ve a por todas hoy!"

Respóndelo directamente, en español, sin introducción.
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['OPENROUTER_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'mistralai/mistral-7b-instruct:free',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.6,
                ]
            ]);

            $result = $response->toArray();
            $content = $result['choices'][0]['message']['content'];

            return new JsonResponse(['respuesta' => $content]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al generar el consejo: ' . $e->getMessage()], 500);
        }
    }
}
