<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'billable_period_days',
        'status',
        'parent_partner_id',
        'team_role',
        'access_level',
        'partner_tier',
        'partner_plan',
        'avg_rating',
        'total_ratings',
        'selection_ratio',
        'closure_rate',
        'repeat_hire_count',
        'vendor_badge',
        'vendor_level',
        'penalty_active',
        'penalty_reason',
    ];

    protected $appends = [];

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

    protected function clientCode(): Attribute
    {
        return Attribute::make(
            get: fn () => 'SH' . (1000 + $this->id),
        );
    }

    protected function entityCode(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->id) {
                    return null;
                }

                $role = strtolower((string) $this->getRoleNames()->first());
                $prefix = match ($role) {
                    'client' => 'CLT',
                    'partner' => 'PRT',
                    'candidate' => 'CND',
                    'superadmin' => 'ADM',
                    'manager' => 'MGR',
                    default => 'USR',
                };

                return sprintf('SH-%s-%06d', $prefix, (int) $this->id);
            },
        );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function candidate(): HasOne
    {
        return $this->hasOne(Candidate::class);
    }

    public function partnerProfile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }

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

    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function clientCommercial(): HasOne
    {
        return $this->hasOne(\App\Models\ClientCommercial::class);
    }

    public function parentPartner()
    {
        return $this->belongsTo(User::class, 'parent_partner_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(User::class, 'parent_partner_id');
    }

    /**
     * The owner id for whichever partner team this user is part of.
     * For an owner this returns their own id; for a team-member, their parent.
     */
    public function partnerOwnerId(): ?int
    {
        return $this->parent_partner_id ?? $this->id;
    }

    public function isPartnerOwner(): bool
    {
        return $this->hasRole('partner') && empty($this->parent_partner_id);
    }

    public function isPartnerTeamMember(): bool
    {
        return $this->hasRole('partner') && !empty($this->parent_partner_id);
    }

    public function canSeeCommercials(): bool
    {
        return $this->isPartnerOwner() || in_array($this->access_level, ['full', null], true);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function isAdminOrManager(): bool
    {
        return $this->hasRole('Superadmin') || $this->hasRole('Manager');
    }
}
