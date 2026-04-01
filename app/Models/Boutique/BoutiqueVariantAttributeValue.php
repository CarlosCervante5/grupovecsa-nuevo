<?php

namespace App\Models\Boutique;

use Illuminate\Database\Eloquent\Model;

class BoutiqueVariantAttributeValue extends Model
{
    protected $table;

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'boutique_variant_attribute_values';
    }

    protected $fillable = ['variant_id', 'attribute_value_id'];
}
