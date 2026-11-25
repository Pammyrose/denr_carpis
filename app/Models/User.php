<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'is_admin',
        'office_scope',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'integer',
    ];

    // Optional: Direct relationship to employee via email (for easier querying)
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'email', 'email');
    }

    // Accessor for full_name (matches Profile.vue)
    public function getFullNameAttribute(): string
    {
        $e = $this->employee;

        if (! $e) {
            return 'Unknown User';
        }

        $parts = array_filter([
            $e->first_name,
            $e->middle_name,
            $e->last_name,
            $e->suffix,
        ]);

        return $parts ? implode(' ', $parts) : 'Unknown User';
    }

    // Accessor for role (matches Profile.vue)
    public function getRoleAttribute(): string
    {
        return match ((int) $this->is_admin) {
            2 => 'Admin',
            1 => 'Super Admin',
            default => 'User',
        };
    }
}
