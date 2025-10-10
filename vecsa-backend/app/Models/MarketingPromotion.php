<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class MarketingPromotion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'marketing_promotions';
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
        'sort_id',
        'spec_sheet',
        'description',
        'image_path',
    ];

    protected $hidden = [
        'id',
        'pivot',
        'updated_at',
        'deleted_at'
    ];

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

    public function campaigns()
    {
        return $this->belongsToMany(MarketingCampaign::class, env('DB_TABLE_PREFIX', '') . 'campaign_promotion',  'promotion_id', 'campaign_id');
    }

    /**
     * Find the campaign by UUID instead of ID
     *
     * @param string $uuid
     * @return \App\Models\Promotion|null
     */
    public static function findByUuid($uuid)
    {
        return self::where('uuid', $uuid)->first();
    }
}
