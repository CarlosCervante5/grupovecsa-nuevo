<?php

namespace App\Models\Boutique;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class BoutiqueProductAttributeValue extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_values';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = ['attribute_id', 'value', 'color_hex', 'sort_order'];

    protected $hidden = ['id', 'attribute_id', 'updated_at'];

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    public static function findByUuid($uuid)
    {
        return self::where('uuid', $uuid)->first();
    }

    public function attribute()
    {
        return $this->belongsTo(BoutiqueProductAttribute::class, 'attribute_id');
    }

    public function variants()
    {
        return $this->belongsToMany(
            BoutiqueProductVariant::class,
            env('DB_TABLE_PREFIX', '') . 'boutique_variant_attribute_values',
            'attribute_value_id',
            'variant_id'
        );
    }
}
