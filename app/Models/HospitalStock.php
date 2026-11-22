<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\HospitalStockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One batch of one vaccine at one hospital.
 * quantity = doses on hand, reserved = held by open bookings, available = the difference.
 * A batch can be used up to and including its expiry date.
 */
// hospital_id and reserved are never filled from request input.
#[Fillable(['vaccine_id', 'batch_number', 'expiry_date', 'quantity'])]
class HospitalStock extends Model
{
    /** @use HasFactory<HospitalStockFactory> */
    use HasFactory;

    protected $table = 'hospital_stock';

    protected $attributes = [
        'reserved' => 0,
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'immutable_date',
            'quantity' => 'integer',
            'reserved' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Hospital, $this>
     */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class)->withTrashed();
    }

    /**
     * @return BelongsTo<Vaccine, $this>
     */
    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(Vaccine::class)->withTrashed();
    }

    /**
     * Doses given from this batch.
     *
     * @return HasMany<ChildDose, $this>
     */
    public function administeredDoses(): HasMany
    {
        return $this->hasMany(ChildDose::class);
    }

    public function available(): int
    {
        return max(0, $this->quantity - $this->reserved);
    }

    public function isExpired(?CarbonImmutable $on = null): bool
    {
        return $this->expiry_date->lt($on ?? CarbonImmutable::today());
    }

    public function isExpiringSoon(?CarbonImmutable $on = null): bool
    {
        $on ??= CarbonImmutable::today();

        return ! $this->isExpired($on)
            && $this->expiry_date->lte($on->addDays(config('vaccination.stock.expiring_soon_days')));
    }

    /**
     * Batches that can still be used on $date: not expired by then.
     *
     * @param  Builder<self>  $query
     */
    public function scopeUsableOn(Builder $query, CarbonImmutable $date): void
    {
        $query->whereDate('expiry_date', '>=', $date);
    }

    /**
     * A batch can be removed only if nothing depends on it.
     */
    public function isRemovable(): bool
    {
        return $this->reserved === 0 && ! $this->administeredDoses()->exists();
    }
}
