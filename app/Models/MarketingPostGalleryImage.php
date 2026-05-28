<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class MarketingPostGalleryImage extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('vecsa.db_table_prefix', '').'marketing_post_gallery_images';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = [
        'sort_id',
        'image_path',
        'image_name',
        'post_id',
    ];

    protected $hidden = [
        'id',
        'post_id',
        'updated_at',
        'deleted_at',
    ];

    public function getCreatedAtAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(MarketingPost::class, 'post_id');
    }

    public static function findByUuid(string $uuid): ?self
    {
        return self::where('uuid', $uuid)->first();
    }
}
