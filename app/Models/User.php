<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\HospitalStatus;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// `role` is deliberately not fillable: it is only ever set explicitly in code,
// so no request can promote itself (e.g. a public signup posting role=admin).
#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /**
     * @return HasOne<Hospital, $this>
     */
    public function hospital(): HasOne
    {
        return $this->hasOne(Hospital::class);
    }

    /**
     * A parent's children.
     *
     * @return HasMany<Child, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Child::class, 'parent_id');
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isHospital(): bool
    {
        return $this->role === Role::Hospital;
    }

    public function isParent(): bool
    {
        return $this->role === Role::Parent;
    }

    /**
     * Why this account may not sign in right now, or null if it may.
     * Only hospital accounts can be blocked: they must be approved and active.
     */
    public function loginBlockedReason(): ?string
    {
        if (! $this->isHospital()) {
            return null;
        }

        return match ($this->hospital?->status) {
            HospitalStatus::Active => null,
            HospitalStatus::Pending => __('Your hospital is awaiting approval by an administrator. You can sign in once it has been approved.'),
            default => __('This hospital account is inactive. Please contact the administrator.'),
        };
    }
}
