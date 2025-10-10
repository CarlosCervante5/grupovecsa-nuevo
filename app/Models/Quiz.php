<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Quiz extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'quizzes';
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
        'name',
        'description',
        'status',
        'values',
        'question_type',
        'element_type',
        'group_name',
        'image_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
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

    public function customers()
    {
        return $this->belongsToMany(Customer::class, env('DB_TABLE_PREFIX', '') . 'customer_accesory',  'accesory_id', 'customer_id');
    }

    public function getValuesAttribute($value)
    {
        return explode(',', $value);
    }

    public function getSelectedValueAttribute()
    {
        return $this->pivot->selected_value ?? null;
    }

    /**
     * Find the Reward by UUID instead of ID
     *
     * @param string $uuid
     * @return \App\Models\Vehicle|null
     */
    public static function findByUuid($uuid, $relationships = [])
    {
        return self::with($relationships)->where('uuid', $uuid)->first();
    }

    /**
     * Find quizzes by an array of UUIDs and load specified relationships.
     *
     * @param array $uuids
     * @param array $relationships
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function quizzesByUuids(array $uuids = [], array $relationships = [])
    {
        return self::with($relationships)->whereIn('uuid', $uuids)->get();
    }

}

