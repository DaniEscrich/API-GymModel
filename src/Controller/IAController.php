<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IAController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/api/ia', name: 'api_ia', methods: ['POST'])]
    public function generatePlan(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $prompt = <<<PROMPT
Eres un experto en fitness y nutrición. Genera dos planes personalizados y breves, ideales para mostrar en una app móvil.

Muestra el resultado en DOS SECCIONES CLARAS y CON FORMATO LIMPIO:

1. PLAN DE ENTRENAMIENTO:
- Lunes: [máximo 3-6 líneas con ejercicios concretos o descanso]
- Martes: ...
- Miércoles: ...
(Hasta el número de días disponibles: {$data['dias']})

2. PLAN DE COMIDAS:
- Lunes:
  - Desayuno: [1 línea]
  - Comida: [1 línea]
  - Merienda: [1 línea]
  - Cena: [1 línea]
- Martes:
  ...
(Hasta domingo)

RESPONDE EN ESPAÑOL. No des explicaciones ni introducciones. Usa saltos de línea. Sé concreto y directo.

DATOS DEL USUARIO:
Sexo: {$data['sexo']}
Edad: {$data['edad']}
Altura: {$data['altura']} cm
Peso: {$data['peso']} kg
Objetivo: {$data['objetivo']}
Nivel: {$data['nivel']}
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
                    'temperature' => 0.7,
                ]
            ]);

            $result = $response->toArray();
            $content = $result['choices'][0]['message']['content'];

            return new JsonResponse(['respuesta' => $content]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al conectar con la IA: ' . $e->getMessage()], 500);
        }
    }
}
