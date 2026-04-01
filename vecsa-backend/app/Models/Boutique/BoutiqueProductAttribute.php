<?php

namespace App\Models\Boutique;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class BoutiqueProductAttribute extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'boutique_product_attributes';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = ['name'];

    protected $hidden = ['id', 'updated_at'];

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

    public function values()
    {
        return $this->hasMany(BoutiqueProductAttributeValue::class, 'attribute_id')
                    ->orderBy('sort_order');
    }

    public function products()
    {
        return $this->belongsToMany(
            BoutiqueProduct::class,
            env('DB_TABLE_PREFIX', '') . 'boutique_product_attribute_product',
            'attribute_id',
            'product_id'
        );
    }
}
