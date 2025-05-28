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
- Lunes: [máximo 3-5 líneas con ejercicios concretos o descanso]
- Martes: ...
- Miércoles: ...
(Hasta el número de días disponibles, si te digo por ejemplo 3 dias solo 3 dias los que tu quieres si es lunes martes miercoles pues solo esos 3, no me digas de más hazlo de estos días : {$data['dias']})

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
            // Plan de emergencia si falla la API
            $planFallback = <<<PLAN
    🏋️ PLAN DE ENTRENAMIENTO:
    - Lunes: Sentadillas, press banca y abdominales.
    - Miércoles: Cardio 30 minutos y flexiones.
    - Viernes: Dominadas, remo con mancuerna y zancadas.
    
    🍽️ PLAN DE COMIDAS:
    - Lunes:
      - Desayuno: Avena con plátano.
      - Comida: Pollo con arroz integral.
      - Merienda: Yogur natural con nueces.
      - Cena: Ensalada mixta y tortilla francesa.
    - Martes:
      - Desayuno: Tostadas con aguacate.
      - Comida: Lentejas con verduras.
      - Merienda: Fruta y queso fresco.
      - Cena: Crema de calabaza y pescado blanco.
    - Miércoles:
      - Desayuno: Batido de proteínas y tostadas.
      - Comida: Pasta integral con atún.
      - Merienda: Galletas integrales y leche.
      - Cena: Verduras al vapor y pechuga de pollo.
    PLAN;

            return new JsonResponse(['respuesta' => $planFallback]);
        }
    }
}

