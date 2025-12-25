<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\Permission;
use Illuminate\Support\Facades\Config;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions', // Added
        'is_active',
        'store_id',
        'active_session_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array', // Auto-cast JSON to array
        ];
    }

    /**
     * Check if user has a specific permission.
     */
    /**
     * Check if user has a specific permission.
     */
    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string|Permission $permission): bool
    {
        // Global Admin Override
        if ($this->role === 'admin') {
            return true;
        }

        if ($permission instanceof Permission) {
            $permission = $permission->value;
        }

        return in_array($permission, $this->effective_permissions);
    }

    /**
     * Get list of all effective permissions for the user (Role defaults + Overrides).
     * Used for Frontend UI flags.
     */
    public function getEffectivePermissionsAttribute(): array
    {
        $effective = []; // Fix undefined variable

        // 1. Get Role Defaults
        $roleConfig = Config::get('role_permission.' . $this->role, []);
        
        // Convert Enums to strings
        foreach ($roleConfig as $perm) {
            if ($perm instanceof \BackedEnum) {
                $effective[] = $perm->value;
            } elseif (is_string($perm)) {
                $effective[] = $perm;
            }
        }

        // 2. Apply Overrides
        if ($this->permissions) {
            foreach ($this->permissions as $perm => $allowed) {
                if ($allowed === true && !in_array($perm, $effective)) {
                    $effective[] = $perm;
                } elseif ($allowed === false && in_array($perm, $effective)) {
                    $effective = array_diff($effective, [$perm]);
                }
            }
        }

        return array_values($effective);
    }
}