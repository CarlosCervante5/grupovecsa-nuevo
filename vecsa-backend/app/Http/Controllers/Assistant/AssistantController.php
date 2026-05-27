<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Services\Assistant\AssistantChatService;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __construct(protected AssistantChatService $chatService) {}

    public function chat(Request $request)
    {
        $payload = $this->chatService->chat($request);

        return response()->json($payload);
    }
}
