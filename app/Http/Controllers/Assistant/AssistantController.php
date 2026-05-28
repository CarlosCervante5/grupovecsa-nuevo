<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Services\Assistant\AssistantChatService;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __construct(protected AssistantChatService $chatService) {}

    public function dealerships()
    {
        return response()->json([
            'dealerships' => $this->chatService->listDealershipsForChat(),
        ]);
    }

    public function chat(Request $request)
    {
        try {
            $payload = $this->chatService->chat($request);

            return response()->json($payload);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validación',
                'errors' => $e->errors(),
                'needs_dealership' => true,
                'dealerships' => $this->chatService->listDealershipsForChat(),
            ], 422);
        }
    }

    public function messages(Request $request)
    {
        try {
            $payload = $this->chatService->pollMessages($request);

            return response()->json($payload);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validación',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function visitorUnreadSummary(Request $request)
    {
        try {
            $data = $request->validate([
                'conversation_uuid' => 'required|string|max:64',
                'session_key' => 'required|string|max:64',
            ]);

            return response()->json(
                $this->chatService->visitorUnreadSummary(
                    $data['conversation_uuid'],
                    $data['session_key']
                )
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validación',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
