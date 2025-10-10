<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class PostContent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'post_contents';
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
        'sort_id',
        'content_type',
        'content_multimedia_1',
        'content_multimedia_2',
        'multimedia_name_1',
        'multimedia_name_2',
        'post_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'post_id',
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
     * Set the type_content attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setTypeContentAttribute($value)
    {
        $this->attributes['type_content'] = strtolower($value);
    }


    public function post()
    {
        return $this->belongsTo(PostContent::class, 'post_id');
    }


    public static function findByUuid($uuid, $relationships = [])
    {
        return self::with($relationships)->where('uuid', $uuid)->first();
    }

}

