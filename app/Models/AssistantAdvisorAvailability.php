<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistantAdvisorAvailability extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = (string) config('vecsa.db_table_prefix', '').'assistant_advisor_availabilities';
    }

    protected $fillable = [
        'user_id',
        'dealership_id',
        'is_available',
        'available_since',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'available_since' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealership(): BelongsTo
    {
        return $this->belongsTo(Dealership::class);
    }
}
