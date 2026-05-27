<?php

namespace App\Http\Controllers\Assistant;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use Illuminate\Http\Request;
class AssistantChatAdminController extends Controller
{
    public function search(Request $request)
    {
        try {
            $perPage = max(1, min((int) $request->input('per_page', 20), 50));
            $page = max(1, (int) $request->input('page', 1));
            $search = trim((string) $request->input('search', ''));

            $query = AssistantConversation::query()
                ->with(['user:id,nickname,email'])
                ->orderByDesc('last_message_at')
                ->orderByDesc('id');

            if ($search !== '') {
                $like = '%'.$search.'%';
                $query->where(function ($q) use ($like) {
                    $q->where('preview', 'like', $like)
                        ->orWhere('visitor_name', 'like', $like)
                        ->orWhere('visitor_email', 'like', $like)
                        ->orWhere('page_url', 'like', $like)
                        ->orWhereHas('messages', fn ($m) => $m->where('content', 'like', $like));
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('last_message_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('last_message_at', '<=', $request->input('date_to'));
            }

            $paginated = $query->paginate($perPage, ['*'], 'page', $page);

            $paginated->getCollection()->transform(fn (AssistantConversation $c) => $this->listRow($c));

            return ApiResponseHelper::apiSuccess(200, 'Conversaciones obtenidas', [
                'conversations' => $paginated,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al listar conversaciones', $e->getMessage(), 500);
        }
    }

    public function detail(Request $request)
    {
        try {
            $data = $request->validate([
                'uuid' => 'required|string|max:64',
            ]);

            $conversation = AssistantConversation::query()
                ->with(['user:id,nickname,email', 'messages'])
                ->where('uuid', $data['uuid'])
                ->first();

            if (! $conversation) {
                return ApiResponseHelper::apiError('Conversación no encontrada', null, 404);
            }

            return ApiResponseHelper::apiSuccess(200, 'Detalle de conversación', [
                'conversation' => [
                    ...$this->listRow($conversation),
                    'session_key' => $conversation->session_key,
                    'ip_address' => $conversation->ip_address,
                    'messages' => $conversation->messages->map(fn (AssistantMessage $m) => [
                        'id' => $m->id,
                        'role' => $m->role,
                        'content' => $m->content,
                        'created_at' => $m->created_at,
                    ])->values(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener conversación', $e->getMessage(), 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function listRow(AssistantConversation $c): array
    {
        $name = $c->visitor_name
            ?? $c->user?->nickname
            ?? 'Visitante';

        return [
            'uuid' => $c->uuid,
            'preview' => $c->preview,
            'visitor_name' => $name,
            'visitor_email' => $c->visitor_email ?? $c->user?->email,
            'page_url' => $c->page_url,
            'messages_count' => (int) $c->messages_count,
            'last_message_at' => $c->last_message_at,
            'created_at' => $c->created_at,
            'is_registered' => (bool) $c->user_id,
        ];
    }
}
