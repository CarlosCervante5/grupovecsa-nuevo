<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class AssistantController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Eres el asistente virtual de Grupo VECSA, concesionario autorizado de BMW, MINI y BMW Motorrad en México. '
                            . 'Ayudas a los clientes con información sobre vehículos, servicios, citas, boutique, rewards y sucursales. '
                            . 'Sucursales: BMW Puebla Angelópolis, BMW Pachuca, BMW Oaxaca, BMW Veracruz. '
                            . 'Responde de forma amable, concisa y profesional. Si no sabes algo, sugiere contactar al equipo. '
                            . 'Responde siempre en español.',
                    ],
                    ['role' => 'user', 'content' => $request->input('message')],
                ],
                'max_tokens' => 300,
                'temperature' => 0.7,
            ]);

            return response()->json([
                'reply' => $result->choices[0]->message->content,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'reply' => 'Lo siento, no pude procesar tu mensaje en este momento. Intenta de nuevo más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
