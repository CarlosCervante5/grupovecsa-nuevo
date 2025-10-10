<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'vehicles';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });

        static::deleting(function ($vehicle) {
            $vehicle->images()->delete();
        });

        static::restoring(function ($vehicle) {
            $vehicle->images()->onlyTrashed()->restore();
        });
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'vin',
        'purchase_date',
        'sale_price',
        'list_price',
        'offer_price',
        'mileage',
        'status',
        'type',
        'fuel_type',
        'category',
        'cylinders',
        'interior_color',
        'exterior_color',
        'transmission',
        'drive_train',
        'page_status',
        'spec_sheet',
        'brand_id',
        'line_id',
        'model_id',
        'version_id',
        'body_id',
        'dealership_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'brand_id',
        'line_id',
        'model_id',
        'version_id',
        'body_id',
        'dealership_id',
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

    /**
     * Set the VIN attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setVinAttribute($value)
    {
        $this->attributes['vin'] = strtoupper($value);
    }

    /**
     * Set the status attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }

    /**
     * Set the category attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setCategoryAttribute($value)
    {
        $this->attributes['category'] = strtolower($value);
    }

    /**
     * Set the cylinders attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setCylindersAttribute($value)
    {
        $this->attributes['cylinders'] = strtolower($value);
    }

    /**
     * Set the interior color attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setInteriorColorAttribute($value)
    {
        $this->attributes['interior_color'] = strtolower($value);
    }

    /**
     * Set the exterior color attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setExteriorColorAttribute($value)
    {
        $this->attributes['exterior_color'] = strtolower($value);
    }

    /**
     * Set the transmission attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setTransmissionAttribute($value)
    {
        $this->attributes['transmission'] = strtolower($value);
    }

    /**
     * Set the drive train attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setDriveTrainAttribute($value)
    {
        $this->attributes['drive_train'] = strtolower($value);
    }

    /**
     * Set the page status attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setPageStatusAttribute($value)
    {
        $this->attributes['page_status'] = strtolower($value);
    }

    public function dealership()
    {
        return $this->hasOne(Dealership::class, 'id', 'dealership_id');
    }

    public function brand()
    {
        return $this->belongsTo(VehicleBrand::class, 'brand_id');
    }

    public function line()
    {
        return $this->belongsTo(BrandLine::class, 'line_id');
    }

    public function model()
    {
        return $this->belongsTo(LineModel::class, 'model_id');
    }

    public function version()
    {
        return $this->belongsTo(ModelVersion::class, 'version_id');
    }

    public function body()
    {
        return $this->belongsTo(VehicleBody::class, 'body_id');
    }

    public function specification()
    {
        return $this->hasOne(VehicleSpecification::class, 'vehicle_id');
    }

    public function images()
    {
        return $this->hasMany(VehicleImage::class, 'vehicle_id')->orderBy('sort_id');
    }

    public function firstImage()
    {
        return $this->hasOne(VehicleImage::class, 'vehicle_id')->orderBy('sort_id');
    }

    public function campaigns()
    {
        return $this->belongsToMany(MarketingCampaign::class, env('DB_TABLE_PREFIX', '') . 'vehicle_campaign',  'vehicle_id', 'campaign_id')->active();

    }

    /**
     * Find the vehicle by UUID instead of ID
     *
     * @param string $uuid
     * @return \App\Models\Vehicle|null
     */
    public static function findByUuid($uuid, $relationships = [])
    {
        return self::with($relationships)->where('uuid', $uuid)->first();
    }


    /**
     * Find vehicles by an array of UUIDs and load specified relationships.
     *
     * @param array $uuids
     * @param array $relationships
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function vehiclesByUuids(array $uuids = [], array $relationships = [])
    {
        return self::with($relationships)->whereIn('uuid', $uuids)->get();
    }

}

