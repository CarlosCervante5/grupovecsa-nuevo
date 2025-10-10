<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Strega\Opportunity;
use App\Models\Valuations\VehicleValuation;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });

        static::deleting(function ($user) {
            $user->userProfile()->delete();
        });

        static::restoring(function ($user) {
            $user->userProfile()->onlyTrashed()->restore();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nickname',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'email_verified_at',
        'updated_at',
        'deleted_at',
        'roles',
        'pivot'
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Find the user by UUID instead of ID
     *
     * @param string $uuid
     * @return \App\Models\User|null
     */
    public static function findByUuid($uuid)
    {
        return self::where('uuid', $uuid)->first();
    }

    /**
     * Find the user by their UserProfile name and role name.
     *
     * @param string $name
     * @param string $role
     * @return \App\Models\User|null
     */
    public static function findByName($name, $role)
    {
        return self::whereHas('userProfile', function ($query) use ($name) {
            $query->where('name', $name);
        })->whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        })->first();
    }

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
     * Find the user by email
     *
     * @param string $uuid
     * @return \App\Models\User|null
     */
    public static function findByEmail($email)
    {
        return self::where('email', $email)->first();
    }


    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(Customer::class);
    }


    public function getRoleProfile()
    {
        $role = $this->getRoleNames()->first();

        $profile = match ($role) {
            'client' => $this->customerProfile()->first(),
            default => $this->userProfile()->first(),
        };

        return [
            'role' => $role,
            'profile' => $profile,
        ];
    }

    public function getStregaRoleProfile()
    {
        $roles = $this->getRoleNames();

        $stregaRoles = $roles->filter(function ($role) {
            return str_starts_with($role, 'strega-');
        });

        $profile = $stregaRoles->isNotEmpty() ? $this->userProfile()->first() : null;

        return [
            'role' => $stregaRoles->first(),
            'profile' => $profile,
        ];
    }

    public function getOrCreateStregaToken()
    {
        $expiresAt = now()->addHours(8);

        $this->tokens()->delete();
        $newToken = $this->createToken('Login: ' . $this->email, ['*'], $expiresAt);
        return $newToken;
    }

    public function getOrCreateToken()
    {
        $expiresAt = now()->addHour();

        $this->tokens()->delete();
        $newToken = $this->createToken('Login: ' . $this->email, ['*'], $expiresAt);
        return $newToken;
    }

    public function valuations()
    {
        return $this->belongsToMany(VehicleValuation::class, env('DB_TABLE_PREFIX', '') . 'user_valuation', 'user_id', 'valuation_id');
    }

    public function opportunities()
    {
        return $this->belongsToMany(Opportunity::class, env('DB_TABLE_PREFIX_STREGA', '') . 'user_opportunity', 'user_id', 'opportunity_id')
                    ->withPivot('user_role_name', 'created_at', 'deleted_at')
                    ->wherePivot('user_role_name', 'strega-manager')
                    ->wherePivotNull('deleted_at')
                    ->orderByPivot('created_at', 'desc');
    }
}
