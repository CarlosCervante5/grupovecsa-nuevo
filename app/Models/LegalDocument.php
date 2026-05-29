<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class LegalDocument extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '').'legales';
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
        'slug',
        'title',
        'body_html',
        'meta_description',
        'is_published',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $hidden = [
        'id',
        'updated_by',
    ];

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function findBySlug(string $slug): ?self
    {
        return self::query()->where('slug', $slug)->first();
    }
}
