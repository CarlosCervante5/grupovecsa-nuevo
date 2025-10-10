<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class CustomerVehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'customer_vehicles';
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
        'vin',
        'mileage',
        'type',
        'cylinders',
        'interior_color',
        'exterior_color',
        'transmission',
        'fuel_type',
        'drive_train',
        'brand_name',
        'model_name',
        'line_name',
        'year',
        'version_name',
        'body_name',
        'customer_id'
    ];

    protected $hidden = [
        'id',
        'customer_id',
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

    /**
     * Find the customer vehicle by UUID instead of ID
     *
     * @param string $uuid
     * @return \App\Models\CustomerVehicle|null
     */
    public static function findByUuid($uuid)
    {
        return self::where('uuid', $uuid)->first();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function rewards()
    {
        return $this->belongsToMany(Reward::class,  env('DB_TABLE_PREFIX', '') . 'customer_reward', 'vehicle_id', 'reward_id');
    }
}
