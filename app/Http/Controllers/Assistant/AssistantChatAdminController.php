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

    public function unreadSummary(Request $request)
    {
        try {
            if (! $this->assistantTablesReady()) {
                return ApiResponseHelper::apiSuccess(200, 'Resumen de no leídos', [
                    'total_unread' => 0,
                    'conversations_with_unread' => 0,
                ]);
            }

            $viewer = $this->resolveViewer($request);
            if (! $viewer) {
                return ApiResponseHelper::apiError('No autenticado', null, 401);
            }

            $scopeIds = $this->dealershipAccess->inventoryDealershipIds($viewer);

            $query = AssistantConversation::query();

            if ($scopeIds !== null) {
                $query->where(function ($q) use ($scopeIds, $viewer) {
                    $q->whereIn('dealership_id', $scopeIds);
                    $q->orWhere('assigned_user_id', $viewer->id);
                });
            }

            $totalUnread = 0;
            $conversationsWithUnread = 0;

            foreach ($query->get() as $conversation) {
                $count = $conversation->countUnreadForStaff();
                if ($count > 0) {
                    $totalUnread += $count;
                    $conversationsWithUnread++;
                }
            }

            return ApiResponseHelper::apiSuccess(200, 'Resumen de no leídos', [
                'total_unread' => $totalUnread,
                'conversations_with_unread' => $conversationsWithUnread,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener no leídos', $e->getMessage(), 500);
        }
    }

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

            $conversation->markStaffRead();
            $conversation->refresh();

            return ApiResponseHelper::apiSuccess(200, 'Detalle de conversación', [
                'conversation' => $this->detailPayload($conversation),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener conversación', $e->getMessage(), 500);
        }
    }

    public function takeOver(Request $request)
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
            if (! $viewer) {
                return ApiResponseHelper::apiError('No autenticado', null, 401);
            }

            $conversation = $this->findConversationForViewer($data['uuid'], $viewer);
            if (! $conversation) {
                return ApiResponseHelper::apiError('Conversación no encontrada', null, 404);
            }

            if (! $conversation->isHumanHandoff()) {
                $dealershipName = $conversation->dealership?->name ?? 'Grupo VECSA';
                AssistantMessage::create([
                    'conversation_id' => $conversation->id,
                    'role' => 'assistant',
                    'content' => 'Un asesor de '.$dealershipName.' ha tomado la conversación. Te atenderá en breve.',
                ]);

                $conversation->update([
                    'human_handoff_at' => now(),
                    'assigned_user_id' => $viewer->id,
                    'messages_count' => (int) $conversation->messages_count + 1,
                    'last_message_at' => now(),
                ]);
            } elseif ((int) $conversation->assigned_user_id !== (int) $viewer->id) {
                $conversation->update(['assigned_user_id' => $viewer->id]);
            }

            $conversation->refresh()->load(['user', 'dealership', 'assignedUser.userProfile', 'messages']);

            return ApiResponseHelper::apiSuccess(200, 'Conversación asignada', [
                'conversation' => $this->detailPayload($conversation),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al tomar la conversación', $e->getMessage(), 500);
        }
    }

    public function reply(Request $request)
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
                'message' => 'required|string|max:2000',
            ]);

            $viewer = $this->resolveViewer($request);
            if (! $viewer) {
                return ApiResponseHelper::apiError('No autenticado', null, 401);
            }

            $conversation = $this->findConversationForViewer($data['uuid'], $viewer);
            if (! $conversation) {
                return ApiResponseHelper::apiError('Conversación no encontrada', null, 404);
            }

            if (! $conversation->isHumanHandoff()) {
                return ApiResponseHelper::apiError(
                    'Debes tomar la conversación antes de responder',
                    null,
                    422
                );
            }

            $text = trim($data['message']);
            AssistantMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'agent',
                'content' => $text,
            ]);

            $conversation->update([
                'messages_count' => (int) $conversation->messages_count + 1,
                'last_message_at' => now(),
                'preview' => \Illuminate\Support\Str::limit($text, 500, ''),
            ]);

            $conversation->refresh()->load(['user', 'dealership', 'assignedUser.userProfile', 'messages']);

            return ApiResponseHelper::apiSuccess(200, 'Mensaje enviado', [
                'conversation' => $this->detailPayload($conversation),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al enviar mensaje', $e->getMessage(), 500);
        }
    }

    public function release(Request $request)
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
            if (! $viewer) {
                return ApiResponseHelper::apiError('No autenticado', null, 401);
            }

            $conversation = $this->findConversationForViewer($data['uuid'], $viewer);
            if (! $conversation) {
                return ApiResponseHelper::apiError('Conversación no encontrada', null, 404);
            }

            if ($conversation->isHumanHandoff()) {
                AssistantMessage::create([
                    'conversation_id' => $conversation->id,
                    'role' => 'assistant',
                    'content' => 'La conversación vuelve al asistente virtual. ¿En qué más puedo ayudarte?',
                ]);

                $conversation->update([
                    'human_handoff_at' => null,
                    'messages_count' => (int) $conversation->messages_count + 1,
                    'last_message_at' => now(),
                ]);
            }

            $conversation->refresh()->load(['user', 'dealership', 'assignedUser.userProfile', 'messages']);

            return ApiResponseHelper::apiSuccess(200, 'Conversación liberada', [
                'conversation' => $this->detailPayload($conversation),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al liberar conversación', $e->getMessage(), 500);
        }
    }

    private function findConversationForViewer(string $uuid, User $viewer): ?AssistantConversation
    {
        $scopeIds = $this->dealershipAccess->inventoryDealershipIds($viewer);

        $conversation = AssistantConversation::query()
            ->with(['user', 'dealership', 'assignedUser.userProfile', 'messages'])
            ->where('uuid', $uuid)
            ->first();

        if (! $conversation) {
            return null;
        }

        if ($scopeIds !== null
            && ! in_array((int) $conversation->dealership_id, $scopeIds, true)
            && (int) $conversation->assigned_user_id !== (int) $viewer->id) {
            return null;
        }

        return $conversation;
    }

    /**
     * @return array<string, mixed>
     */
    private function detailPayload(AssistantConversation $conversation): array
    {
        return [
            ...$this->listRow($conversation),
            'session_key' => $conversation->session_key,
            'ip_address' => $conversation->ip_address,
            'messages' => $conversation->messages->map(fn (AssistantMessage $m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at,
            ])->values(),
        ];
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
            'is_human_handoff' => $c->isHumanHandoff(),
            'human_handoff_at' => $c->human_handoff_at,
            'unread_count' => $c->countUnreadForStaff(),
        ];
    }
}
