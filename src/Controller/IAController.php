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
        $tipo = $data['tipo'] ?? 'entrenamiento';

        $diasTexto = $data['dias'] ?? 3;

        if ($tipo === 'entrenamiento') {
            $prompt = <<<PROMPT
Eres un entrenador personal profesional. Genera un PLAN DE ENTRENAMIENTO en formato PLANO y ESTRICTO para una app Android. NO incluyas explicaciones, enlaces, gifs, imágenes, emojis ni texto adicional. SOLO EL PLAN.

🎯 FORMATO REQUERIDO:
- Cada día empieza en una línea aparte con "DÍA X:" (todo en mayúsculas).
- Las líneas siguientes deben tener este formato:
  - Nombre del ejercicio | repeticiones x series 

📌 IMPORTANTE: Genera exactamente {$diasTexto} días, ni más ni menos.

✅ EJEMPLO DE SALIDA:
DÍA 1:
- Flexiones | 15 reps x3 
- Sentadillas | 12 reps x3
DÍA 2:
- Plancha | 3x30s

📋 DATOS DEL USUARIO:
Sexo: {$data['sexo']}
Edad: {$data['edad']}
Altura: {$data['altura']} cm
Peso: {$data['peso']} kg
Objetivo: {$data['objetivo']}
Nivel: {$data['nivel']}
Días de entrenamiento: {$data['dias']}
PROMPT;
        } elseif ($tipo === 'comida') {
            $prompt = <<<PROMPT
Eres un nutricionista profesional. Genera un PLAN DE COMIDAS semanal en formato PLANO, SIMPLE y CLARO para una app Android. No incluyas explicaciones, imágenes, emojis ni texto adicional. SOLO EL PLAN.

🍽️ FORMATO REQUERIDO:
- Cada bloque comienza con "DÍA X:" en mayúsculas.
- Justo debajo, lista las comidas del día en este formato:
  - Desayuno | ...
  - Comida | ...
  - Merienda | ...
  - Cena | ...

📌 IMPORTANTE: Genera exactamente {$diasTexto} días, ni más ni menos.

✅ EJEMPLO DE SALIDA:
DÍA 1:
- Desayuno | Avena con plátano
- Comida | Arroz con pollo
- Merienda | Yogur natural con nueces
- Cena | Ensalada de atún

📋 DATOS DEL USUARIO:
Sexo: {$data['sexo']}
Edad: {$data['edad']}
Altura: {$data['altura']} cm
Peso: {$data['peso']} kg
Objetivo: {$data['objetivo']}
Nivel: {$data['nivel']}
Días de entrenamiento: {$data['dias']}
PROMPT;
        } else {
            return new JsonResponse(['error' => 'Tipo no válido'], 400);
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.6,
                ],
            ]);

            $result = $response->toArray();
            $content = $result['choices'][0]['message']['content'];

            return new JsonResponse(['respuesta' => $content]);
        } catch (\Exception $e) {
            $fallback = <<<PLAN
DÍA 1:
- Sentadillas | 3x12
- Flexiones | 3x10

DÍA 2:
- Zancadas | 3x12
- Plancha | 3x30s
PLAN;

            return new JsonResponse(['respuesta' => $fallback]);
        }
    }
}
