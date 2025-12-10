<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- ADD THIS IMPORT

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'billable_period_days',
        'status',
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
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function partnerProfile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }

    // --- NEW SUB-ADMIN RELATIONSHIPS ---

    /**
     * The clients assigned to this Manager (Sub-Admin).
     */
    public function assignedClients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_manager', 'manager_id', 'client_id')
                    ->withTimestamps();
    }

    /**
     * The managers assigned to this Client.
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_manager', 'client_id', 'manager_id')
                    ->withTimestamps();
    }

    /**
     * Helper to check admin access
     */
    public function isAdminOrManager(): bool
    {
        return $this->hasRole('Superadmin') || $this->hasRole('Manager');
    }
}