<?php

namespace App\Models\Boutique;

use App\Helpers\RichTextHelper;
use App\Models\Dealership;
use Carbon\Carbon;
use App\Services\Boutique\BoutiqueProductPublicationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class BoutiqueProduct extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'boutique_products';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = [
        'category_id',
        'dealership_id',
        'name',
        'description',
        'price',
        'sku',
        'stock',
        'active',
    ];

    protected $hidden = [
        'id',
        'pivot',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
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

    public function category()
    {
        return $this->belongsTo(BoutiqueCategory::class, 'category_id');
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class, 'dealership_id');
    }

    public function images()
    {
        return $this->hasMany(BoutiqueProductImage::class, 'product_id');
    }

    /** Productos visibles en inventario público (activos, con imagen y stock). */
    public function scopePublished(Builder $query): Builder
    {
        return BoutiqueProductPublicationService::applyPublishedScope($query);
    }

    public function variants()
    {
        return $this->hasMany(BoutiqueProductVariant::class, 'product_id')
                    ->where('active', true)
                    ->orderBy('color')
                    ->orderBy('size');
    }

    public function attributes()
    {
        return $this->belongsToMany(
            BoutiqueProductAttribute::class,
            env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_product',
            'product_id',
            'attribute_id'
        );
    }

    public function allVariants()
    {
        return $this->hasMany(BoutiqueProductVariant::class, 'product_id');
    }

    public function cartItems()
    {
        return $this->hasMany(BoutiqueCartItem::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(BoutiqueOrderItem::class, 'product_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(BoutiqueInventoryMovement::class, 'product_id');
    }
}
