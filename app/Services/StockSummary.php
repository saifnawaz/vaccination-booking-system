<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\HospitalStock;
use App\Models\Vaccine;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Per-vaccine stock totals and warnings for one hospital, from its batches.
 *
 * usable   available doses (quantity - reserved) in batches that have not expired
 * low      the hospital stocks this vaccine but usable < config('vaccination.stock.low_threshold')
 */
final class StockSummary
{
    /** @var Collection<int, array{vaccine: Vaccine, usable: int, batches: int, expiring: int, expired: int, low: bool}> */
    public readonly Collection $vaccines;

    public function __construct(Hospital $hospital, ?CarbonImmutable $today = null)
    {
        $today ??= CarbonImmutable::today();
        $threshold = config('vaccination.stock.low_threshold');

        $this->vaccines = $hospital->stock()
            ->with('vaccine')
            ->get()
            ->groupBy('vaccine_id')
            ->map(function (Collection $batches) use ($today, $threshold) {
                $usable = $batches->reject(fn (HospitalStock $b) => $b->isExpired($today))
                    ->sum(fn (HospitalStock $b) => $b->available());

                return [
                    'vaccine' => $batches->first()->vaccine,
                    'usable' => $usable,
                    'batches' => $batches->count(),
                    'expiring' => $batches->filter(fn (HospitalStock $b) => $b->isExpiringSoon($today) && $b->quantity > 0)->count(),
                    'expired' => $batches->filter(fn (HospitalStock $b) => $b->isExpired($today) && $b->quantity > 0)->count(),
                    'low' => $usable < $threshold,
                ];
            })
            ->sortBy(fn (array $row) => $row['vaccine']->name)
            ->values();
    }

    public function usableDoses(): int
    {
        return $this->vaccines->sum('usable');
    }

    public function lowStockCount(): int
    {
        return $this->vaccines->where('low', true)->count();
    }

    public function expiringBatchCount(): int
    {
        return $this->vaccines->sum('expiring');
    }

    public function expiredBatchCount(): int
    {
        return $this->vaccines->sum('expired');
    }
}
