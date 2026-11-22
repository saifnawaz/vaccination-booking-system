<?php

namespace App\Models;

use Database\Factories\VaccineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'code', 'description', 'is_active'])]
class Vaccine extends Model
{
    /** @use HasFactory<VaccineFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<VaccineSchedule, $this>
     */
    public function scheduleDoses(): HasMany
    {
        return $this->hasMany(VaccineSchedule::class)->orderBy('dose_number');
    }

    /**
     * @return HasMany<HospitalStock, $this>
     */
    public function stock(): HasMany
    {
        return $this->hasMany(HospitalStock::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Vaccines in the routine schedule are never deleted, only deactivated:
     * children's schedules are built from them.
     */
    public function isScheduled(): bool
    {
        return $this->scheduleDoses()->exists();
    }
}
