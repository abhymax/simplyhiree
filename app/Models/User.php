<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute; // <--- Import Attribute

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
    
    // --- NEW ACCESSOR FOR CLIENT ID (Starts SH1000) ---
    /**
     * Get the formatted Client ID (e.g., SH1005).
     */
    protected function clientCode(): Attribute
    {
        return Attribute::make(
            get: fn () => 'SH' . (1000 + $this->id),
        );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function partnerProfile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }
    
    // ... [Rest of the relationships remain unchanged] ...

    public function assignedClients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_manager', 'manager_id', 'client_id')
                    ->withTimestamps();
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_manager', 'client_id', 'manager_id')
                    ->withTimestamps();
    }

    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function isAdminOrManager(): bool
    {
        return $this->hasRole('Superadmin') || $this->hasRole('Manager');
    }
}