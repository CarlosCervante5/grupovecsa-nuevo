<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'customers';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = [
        'honorific',
        'bp_id',
        'crm_id',
        'rfc',
        'tax_regime',
        'name',
        'last_name',
        'age',
        'birthday',
        'gender',
        'phone_1',
        'phone_2',
        'phone_3',
        'cellphone',
        'email_1',
        'email_2',
        'contact_method',
        'zip_code',
        'address',
        'state',
        'city',
        'district',
        'neighborhood',
        'marital_status',
        'educational_level',
        'picture',
        'origin_agency',
        'user_id'
    ];

    protected $hidden = [
        'id',
        'user_id',
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
     * Find the customer by UUID instead of ID
     *
     * @param string $uuid
     * @return \App\Models\Customer
     */
    public static function findByUuid($uuid, $relationships = [])
    {
        return self::with($relationships)->where('uuid', $uuid)->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customerVehicles()
    {
        return $this->hasMany(CustomerVehicle::class);
    }

    public function customerRewards()
    {
        return $this->hasMany(CustomerReward::class, 'customer_id');
    }

    public function rewards()
    {
        return $this->belongsToMany(Reward::class, env('DB_TABLE_PREFIX', '') . 'customer_reward',  'customer_id', 'reward_id');
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, env('DB_TABLE_PREFIX', '') . 'customer_quiz',  'customer_id', 'quiz_id')->withPivot('selected_value')->orderBy('sort_id');
    }

    public function relations()
    {
        return $this->belongsToMany(Customer::class, env('DB_TABLE_PREFIX', '') . 'customer_relations', 'customer_id', 'related_customer_id');
    }
}
