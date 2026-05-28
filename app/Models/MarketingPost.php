<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class MarketingPost extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('vecsa.db_table_prefix', '').'marketing_posts';
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
        'status',
        'title',
        'excerpt',
        'body_html',
        'image_path',
        'url_name',
        'category',
        'wp_import_id',
        'wp_category_label',
        'wp_tags',
        'wp_featured_source_url',
        'event_begin_date',
        'event_end_date',
        'experience_post_type',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'wp_tags' => 'array',
        'event_begin_date' => 'date',
        'event_end_date' => 'date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'updated_at',
        'deleted_at',
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
     * Set the url_name attribute to lowercase.
     *
     * @param  string  $value
     * @return void
     */
    protected function setUrlNameAttribute($value)
    {
        $this->attributes['url_name'] = strtolower($value);
    }

    public function contents()
    {
        return $this->hasMany(PostContent::class, 'post_id')->orderBy('sort_id');
    }

    public function galleryImages()
    {
        return $this->hasMany(MarketingPostGalleryImage::class, 'post_id')->orderBy('sort_id');
    }

    public function isExperienceGallery(): bool
    {
        return ($this->experience_post_type ?? 'story') === 'gallery';
    }

    public static function findByUuid($uuid, $relationships = [])
    {
        return self::with($relationships)->where('uuid', $uuid)->first();
    }
}
