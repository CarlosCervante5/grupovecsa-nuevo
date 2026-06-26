<?php

namespace App\Models\CarCare;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class CarCareBanner extends Model
{
    use HasFactory, SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '').'carcare_banners';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = [
        'title',
        'subtitle',
        'disclaimer',
        'desktop_image_path',
        'mobile_image_path',
        'active',
        'sort_id',
    ];

    protected $hidden = ['id', 'pivot', 'updated_at', 'deleted_at'];

    protected $casts = ['active' => 'boolean'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function getCreatedAtAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public static function findByUuid(string $uuid): ?self
    {
        return self::where('uuid', $uuid)->first();
    }
}
