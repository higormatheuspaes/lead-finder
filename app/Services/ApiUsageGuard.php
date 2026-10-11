<?php

namespace App\Services;

use App\Exceptions\MonthlyLimitReached;
use App\Models\ApiUsage;
use Illuminate\Support\Facades\DB;

class ApiUsageGuard
{
    /** Conta uma chamada do mês; lança MonthlyLimitReached se já chegou no limite. */
    public function consume(string $provider, int $limit): void
    {
        DB::transaction(function () use ($provider, $limit) {
            $usage = ApiUsage::lockForUpdate()->firstOrCreate(
                ['provider' => $provider, 'period' => now()->format('Y-m')],
                ['requests' => 0],
            );

            if ($usage->requests >= $limit) {
                throw new MonthlyLimitReached("Limite mensal de {$limit} chamadas atingido.");
            }

            $usage->increment('requests');
        });
    }

    public function used(string $provider): int
    {
        return (int) ApiUsage::where('provider', $provider)
            ->where('period', now()->format('Y-m'))
            ->value('requests');
    }
}
