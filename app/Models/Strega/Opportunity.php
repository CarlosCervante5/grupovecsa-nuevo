<?php

namespace App\Models\Strega;

use App\Models\Customer;
use App\Models\CustomerAppointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Opportunity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX_STREGA', '') . 'opportunities';
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
        'status',
        'description',
        'category',
        'dealership_name',
        'created_at',
        'campaign_id',
        'customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'pivot',
        'campaign_id',
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
     * Set the type attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setTypeAttribute($value)
    {
        $this->attributes['type'] = strtolower($value);
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
     * Set the dealership name attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setDealershipNameAttribute($value)
    {
        $this->attributes['dealership_name'] = strtolower($value);
    }

    /**
     * Find the Opportunity by UUID instead of ID
     *
     * @param string $uuid
     * @return \App\Models\Vehicle|null
     */
    public static function findByUuid($uuid, $relationships = [])
    {
        return self::with($relationships)->where('uuid', $uuid)->first();
    }

    public function forms()
    {
        return $this->belongsToMany(Form::class, env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_form', 'opportunity_id', 'form_id')->withPivot('selected_value')->orderBy('sort_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function manager()
    {
        return $this->belongsToMany(User::class, env('DB_TABLE_PREFIX_STREGA', '') . 'user_opportunity', 'opportunity_id', 'user_id')
                    ->withPivot('user_role_name','created_at')
                    ->wherePivot('user_role_name', 'strega-manager')
                    ->wherePivotNull('deleted_at')
                    ->orderByPivot('created_at', 'desc');
    }

    public function appointments()
    {
        return $this->belongsToMany(CustomerAppointment::class, env('DB_TABLE_PREFIX_STREGA', '') . 'opportunity_appointment', 'opportunity_id', 'appointment_id');
    }

    public function firstContactAttempts(){
        return $this->hasMany(ContactAttempt::class, 'opportunity_id')->where('type','first_contact');
    }

    public function experienceContactAttempts(){
        return $this->hasMany(ContactAttempt::class, 'opportunity_id')->where('type','follow_attempt');
    }
}