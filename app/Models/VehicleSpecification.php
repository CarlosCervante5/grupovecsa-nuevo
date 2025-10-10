<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleSpecification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'vehicle_specifications';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'keys_number',
        'wheel_locks',
        'spare_wheel',
        'hydraulic_jack',
        'fire_extinguisher',
        'reflectors',
        'jumper_cables',
        'engine_type',
        'plates',
        'country_of_origin',
        'auto_start_stop',
        'intake_engine',
        'tools', 
        'antenna', 
        'stud_wrench', 
        'security_film', 
        'warranty_policy', 
        'warranty_manual', 
        'vehicle_id'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
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
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setSpareWheelAttribute($value)
    {
        $this->attributes['spare_wheel'] = strtolower($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setHydraulicJackAttribute($value)
    {
        $this->attributes['hydraulic_jack'] = strtolower($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setFireExtinguisherAttribute($value)
    {
        $this->attributes['fire_extinguisher'] = strtolower($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setReflectorsAttribute($value)
    {
        $this->attributes['reflectors'] = strtolower($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setJumperCablesAttribute($value)
    {
        $this->attributes['jumper_cables'] = strtolower($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setEngineTypeAttribute($value)
    {
        $this->attributes['engine_type'] = strtolower($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setPlatesAttribute($value)
    {
        $this->attributes['plates'] = strtoupper($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setCountryOfOriginAttribute($value)
    {
        $this->attributes['country_of_origin'] = strtoupper($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setAutoStartStopAttribute($value)
    {
        $this->attributes['auto_start_stop'] = strtoupper($value);
    }

    /**
     * Set the attributes.
     *
     * @return array<string, string>
     */
    public function setIntakeEngineAttribute($value)
    {
        $this->attributes['intake_engine'] = strtoupper($value);
    }


    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    
}
