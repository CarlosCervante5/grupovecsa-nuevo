<?php

namespace App\Http\Controllers\Assistant;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Models\User;
use App\Services\DealershipAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

class AssistantChatAdminController extends Controller
{
    public function __construct(
        private readonly DealershipAccessService $dealershipAccess
    ) {}

    public function search(Request $request)
    {
        try {
            if (! $this->assistantTablesReady()) {
                return ApiResponseHelper::apiError(
                    'El módulo de chats del asistente no está disponible (falta migración en el servidor)',
                    null,
                    503,
                    'ASSISTANT_TABLES_MISSING'
                );
            }

            $perPage = max(1, min((int) $request->input('per_page', 20), 50));
            $page = max(1, (int) $request->input('page', 1));
            $search = trim((string) $request->input('search', ''));

            $viewer = $this->resolveViewer($request);
            $scopeIds = $this->dealershipAccess->inventoryDealershipIds($viewer);

            $query = AssistantConversation::query()
                ->with(['user', 'dealership', 'assignedUser.userProfile'])
                ->orderByDesc('last_message_at')
                ->orderByDesc('id');

            if ($scopeIds !== null) {
                $query->where(function ($q) use ($scopeIds, $viewer) {
                    $q->whereIn('dealership_id', $scopeIds);
                    if ($viewer) {
                        $q->orWhere('assigned_user_id', $viewer->id);
                    }
                });
            }

            if ($search !== '') {
                $like = '%'.$search.'%';
                $query->where(function ($q) use ($like) {
                    $q->where('preview', 'like', $like)
                        ->orWhere('visitor_name', 'like', $like)
                        ->orWhere('visitor_email', 'like', $like)
                        ->orWhere('page_url', 'like', $like)
                        ->orWhereHas('messages', fn ($m) => $m->where('content', 'like', $like))
                        ->orWhereHas('dealership', fn ($d) => $d->where('name', 'like', $like));
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
            if (! $this->assistantTablesReady()) {
                return ApiResponseHelper::apiError(
                    'El módulo de chats del asistente no está disponible (falta migración en el servidor)',
                    null,
                    503,
                    'ASSISTANT_TABLES_MISSING'
                );
            }

            $data = $request->validate([
                'uuid' => 'required|string|max:64',
            ]);

            $viewer = $this->resolveViewer($request);
            $scopeIds = $this->dealershipAccess->inventoryDealershipIds($viewer);

            $conversation = AssistantConversation::query()
                ->with(['user', 'dealership', 'assignedUser.userProfile', 'messages'])
                ->where('uuid', $data['uuid'])
                ->first();

            if (! $conversation) {
                return ApiResponseHelper::apiError('Conversación no encontrada', null, 404);
            }

            if ($scopeIds !== null
                && ! in_array((int) $conversation->dealership_id, $scopeIds, true)
                && (int) $conversation->assigned_user_id !== (int) ($viewer?->id)) {
                return ApiResponseHelper::apiError('No tienes permiso para ver esta conversación', null, 403);
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

    private function resolveViewer(Request $request): ?User
    {
        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }
        $accessToken = PersonalAccessToken::findToken($token);
        $model = $accessToken?->tokenable;

        return $model instanceof User ? $model : null;
    }

    private function assistantTablesReady(): bool
    {
        try {
            return Schema::hasTable((new AssistantConversation)->getTable())
                && Schema::hasTable((new AssistantMessage)->getTable());
        } catch (\Throwable) {
            return false;
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

        $assigned = $c->assignedUser;
        $profile = $assigned?->userProfile;
        $assignedName = $profile
            ? trim(($profile->name ?? '').' '.($profile->last_name ?? ''))
            : ($assigned?->nickname ?? null);

        return [
            'uuid' => $c->uuid,
            'preview' => $c->preview,
            'visitor_name' => $name,
            'visitor_email' => $c->visitor_email ?? $c->user?->email,
            'page_url' => $c->page_url,
            'dealership_id' => $c->dealership_id,
            'dealership_name' => $c->dealership?->name,
            'assigned_user_id' => $c->assigned_user_id,
            'assigned_user_name' => $assignedName !== '' ? $assignedName : null,
            'messages_count' => (int) $c->messages_count,
            'last_message_at' => $c->last_message_at,
            'created_at' => $c->created_at,
            'is_registered' => (bool) $c->user_id,
        ];
    }
}
