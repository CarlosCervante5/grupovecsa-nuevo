<?php

namespace App\Models\Valuations;

use App\Models\CustomerAppointment;
use App\Models\Dealership;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class VehicleValuation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'vehicle_valuations';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_trade_in_offer',
        'book_sale_price',
        'intellimotors_trade_in_offer',
        'intellimotors_sale_price',
        'labor_cost',
        'spare_parts_cost',
        'body_work_painting_cost',
        'estimated_total',
        'trade_in_final',
        'final_offer',
        'status',
        'status_repairs',
        'status_parts',
        'status_acquisition',
        'acquisition_pdf',
        'comments',
        'vehicle_id',
        'dealership_id',
        'appointment_id',
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'pivot',
        'dealership_id',
        'appointment_id',
        'vehicle_id',
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

    public static function findByUuid($uuid, $relationships = [])
    {
        return self::with($relationships)->where('uuid', $uuid)->first();
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class, 'dealership_id');
    }

    public function appointment()
    {
        return $this->belongsTo(CustomerAppointment::class, 'appointment_id');
    }

    public function spareParts()
    {
        return $this->hasMany(ValuationPart::class, 'valuation_id');
    }

    public function repairs()
    {
        return $this->hasMany(ValuationRepair::class, 'valuation_id');
    }

    public function checkpoints()
    {
        return $this->belongsToMany(ValuationCheckpoint::class, env('DB_TABLE_PREFIX', '') . 'valuation_checkpoint', 'valuation_id', 'checkpoint_id')->withPivot('selected_value')->orderBy('sort_id');
    }

    public function acquisition_checkpoints()
    {
        return $this->belongsToMany(AcquisitionCheckpoint::class, env('DB_TABLE_PREFIX', '') . 'acquisition_checkpoint', 'valuation_id', 'checkpoint_id')->withPivot('selected_value')->orderBy('sort_id');
    }

    public function images()
    {
        return $this->hasMany(ValuationRepair::class, 'valuation_id');
    }

    public function valuator()
    {
        return $this->belongsToMany(User::class, env('DB_TABLE_PREFIX', '') . 'user_valuation', 'valuation_id', 'user_id')
                    ->withPivot('user_role_name')
                    ->wherePivot('user_role_name', 'valuator');
    }

    public function seller()
    {
        return $this->belongsToMany(User::class, env('DB_TABLE_PREFIX', '') . 'user_valuation', 'valuation_id', 'user_id')
                    ->withPivot('user_role_name')
                    ->wherePivot('user_role_name', 'seller');
    }

    public function technician()
    {
        return $this->belongsToMany(User::class, env('DB_TABLE_PREFIX', '') . 'user_valuation', 'valuation_id', 'user_id')
                    ->withPivot('user_role_name')
                    ->wherePivot('user_role_name', 'technician');
    }
}
