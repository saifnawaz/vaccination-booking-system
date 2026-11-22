<?php

namespace App\Models;

use App\Enums\HospitalStatus;
use Database\Factories\HospitalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

// status, approved_by and approved_at are only changed through transitionTo().
#[Fillable(['name', 'address', 'city', 'phone', 'license_number'])]
class Hospital extends Model
{
    /** @use HasFactory<HospitalFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => HospitalStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', HospitalStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status === HospitalStatus::Active;
    }

    /**
     * Move to a new status, enforcing the allowed transitions.
     * Approving (pending → active) records who approved it and when.
     *
     * @throws InvalidArgumentException when the transition is not allowed
     */
    public function transitionTo(HospitalStatus $next, User $by): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw new InvalidArgumentException(
                "Cannot change hospital status from {$this->status->value} to {$next->value}."
            );
        }

        if ($this->status === HospitalStatus::Pending && $next === HospitalStatus::Active) {
            $this->approved_by = $by->id;
            $this->approved_at = now();
        }

        $this->status = $next;
        $this->save();
    }
}
