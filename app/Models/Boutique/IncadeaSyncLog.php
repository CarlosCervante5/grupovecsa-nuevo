<?php

namespace App\Models\Boutique;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class IncadeaSyncLog extends Model
{
    use HasFactory;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'incadea_sync_logs';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = [
        'user_id',
        'status',
        'total_fetched',
        'total_created',
        'total_updated',
        'total_skipped',
        'total_errors',
        'filters_applied',
        'error_details',
        'started_at',
        'finished_at',
    ];

    protected $hidden = [
        'id',
        'pivot',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'filters_applied' => 'array',
        'error_details' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at', 'started_at', 'finished_at'];

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getStartedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getFinishedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public static function findByUuid($uuid)
    {
        return self::where('uuid', $uuid)->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
