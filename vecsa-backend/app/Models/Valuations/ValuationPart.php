<?php

namespace App\Models\Valuations;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class ValuationPart extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'valuation_parts';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    protected $fillable = [
        'code',
        'name',
        'labor_time',
        'quantity',
        'valuation_id',
        'status',
    ];

    protected $hidden = [
        'id',
        'valuation_id',
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

    public function valuation()
    {
        return $this->belongsTo(VehicleValuation::class, 'valuation_id');
    }

    public function partSupplierOriginal()
    {
        return $this->hasOne(PartSupplier::class, 'part_id')
                ->where('quote_type', 'original')
                ->orderBy('created_at', 'asc');
    }

    public function suppliers()
    {
        return $this->belongsToMany(SpareSupplier::class, env('DB_TABLE_PREFIX', '') . 'part_supplier', 'part_id', 'supplier_id')->withPivot(['quote_type','delivery_date','cost']);
    }

}
