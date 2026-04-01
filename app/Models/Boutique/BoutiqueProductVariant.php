<?php

namespace App\Models\Boutique;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class BoutiqueProductVariant extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'boutique_product_variants';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = [
        'product_id',
        'color',
        'color_hex',
        'size',
        'sku',
        'price',
        'stock',
        'active',
    ];

    protected $hidden = ['id', 'product_id', 'updated_at'];

    protected $casts = [
        'active' => 'boolean',
        'stock'  => 'integer',
        'price'  => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(BoutiqueProduct::class, 'product_id');
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            BoutiqueProductAttributeValue::class,
            env('DB_TABLE_PREFIX', '') . 'boutique_variant_attribute_values',
            'variant_id',
            'attribute_value_id'
        );
    }

    public function getEffectivePriceAttribute()
    {
        return $this->price ?? $this->product->price;
    }
}
