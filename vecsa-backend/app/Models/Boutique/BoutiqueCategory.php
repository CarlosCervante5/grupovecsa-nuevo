<?php

namespace App\Models\Boutique;

use App\Helpers\RichTextHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class BoutiqueCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'boutique_categories';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = [
        'name',
        'description',
        'active',
        'parent_id',
    ];

    protected $hidden = [
        'id',
        'pivot',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => RichTextHelper::toPlainText($value),
            set: fn (?string $value) => RichTextHelper::toPlainText($value),
        );
    }

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getDeletedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public static function findByUuid($uuid)
    {
        return self::where('uuid', $uuid)->first();
    }

    /**
     * IDs de la categoría y todas sus descendientes (por parent_id), para filtros de productos/catálogo.
     *
     * @return int[]
     */
    public static function idsSelfAndDescendants(int $categoryId): array
    {
        $ids = [$categoryId];
        $queue = [$categoryId];
        while ($queue !== []) {
            $parentId = (int) array_shift($queue);
            $childIds = self::query()->where('parent_id', $parentId)->pluck('id')->all();
            foreach ($childIds as $cid) {
                $cid = (int) $cid;
                if (! in_array($cid, $ids, true)) {
                    $ids[] = $cid;
                    $queue[] = $cid;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    public function parent()
    {
        return $this->belongsTo(BoutiqueCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BoutiqueCategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(BoutiqueProduct::class, 'category_id');
    }
}
