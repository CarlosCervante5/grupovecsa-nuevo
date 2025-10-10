<?php

namespace App\Models;

use App\Models\Strega\ContactAttempt;
use App\Models\Strega\FollowUpSurvey;
use App\Models\Strega\Opportunity;
use App\Models\Valuations\VehicleValuation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class CustomerAppointment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'customer_appointments';
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
        'type',
        'description',
        'scheduled_date',
        'attendance_date',
        'dealership_name',
        'departure_date',
        'status',
        'customer_id',
        'vehicle_id',
        'prev_appointment_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'pivot',
        'customer_id',
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
 
    /**
     * Find the appointment by UUID instead of ID
     *
     * @param string $uuid
     * @return \App\Models\CustomerAppointment|null
     */
    public static function findByUuid($uuid, $relationships = [])
    {
        return self::with($relationships)->where('uuid', $uuid)->first();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(CustomerVehicle::class, 'vehicle_id');
    }

    public function contactAttempts()
    {
        return $this->hasMany(ContactAttempt::class, 'appointment_id')->where('type','first_contact');
    }

    public function followAttempts()
    {
        return $this->hasMany(ContactAttempt::class, 'appointment_id')->where('type','follow_attempt');
    }

    /**
     *
     * @return \App\Models\Valuations\VehicleValuation
     */
    public function valuation(): HasOne
    {
        return $this->hasOne(VehicleValuation::class, 'appointment_id');
    }

    public function opportunities()
    {
        return $this->belongsToMany(Opportunity::class, env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_appointment', 'appointment_id', 'opportunity_id');
    }

    public function surveys()
    {
        return $this->belongsToMany(FollowUpSurvey::class, env('DB_TABLE_PREFIX_STREGA', '') . 'appointment_survey', 'appointment_id', 'survey_id')->withPivot('selected_value')->orderBy('sort_id');
    }

    public function stregaSeller()
    {
        return $this->belongsToMany(User::class, env('DB_TABLE_PREFIX', '') . 'user_appointment', 'appointment_id', 'user_id')
                    ->withPivot('user_role_name')
                    ->wherePivot('user_role_name', 'strega-seller');
    }

}