<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class AssistantConversation extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = (string) config('vecsa.db_table_prefix', '').'assistant_conversations';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Uuid::uuid4();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'session_key',
        'user_id',
        'dealership_id',
        'assigned_user_id',
        'human_handoff_at',
        'staff_last_read_message_id',
        'visitor_last_read_message_id',
        'visitor_name',
        'visitor_email',
        'page_url',
        'ip_address',
        'preview',
        'messages_count',
        'last_message_at',
    ];

    protected $casts = [
        'messages_count' => 'integer',
        'last_message_at' => 'datetime',
        'human_handoff_at' => 'datetime',
        'staff_last_read_message_id' => 'integer',
        'visitor_last_read_message_id' => 'integer',
    ];

    public function countUnreadForStaff(): int
    {
        $lastRead = (int) ($this->staff_last_read_message_id ?? 0);

        return (int) $this->messages()
            ->where('role', 'user')
            ->where('id', '>', $lastRead)
            ->count();
    }

    public function countUnreadForVisitor(): int
    {
        $lastRead = (int) ($this->visitor_last_read_message_id ?? 0);

        return (int) $this->messages()
            ->whereIn('role', ['agent', 'assistant'])
            ->where('id', '>', $lastRead)
            ->count();
    }

    public function markStaffRead(): void
    {
        $maxId = (int) ($this->messages()->max('id') ?? 0);
        if ($maxId > (int) ($this->staff_last_read_message_id ?? 0)) {
            $this->update(['staff_last_read_message_id' => $maxId]);
        }
    }

    public function markVisitorRead(?int $messageId = null): void
    {
        $maxId = $messageId ?? (int) ($this->messages()->max('id') ?? 0);
        if ($maxId > (int) ($this->visitor_last_read_message_id ?? 0)) {
            $this->update(['visitor_last_read_message_id' => $maxId]);
        }
    }

    public function isHumanHandoff(): bool
    {
        return $this->human_handoff_at !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AssistantMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public static function findByUuid(string $uuid): ?self
    {
        return self::query()->where('uuid', $uuid)->first();
    }

    public function getCreatedAtAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getUpdatedAtAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getLastMessageAtAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }
}
