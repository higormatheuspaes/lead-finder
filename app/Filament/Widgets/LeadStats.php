<?php

namespace App\Filament\Widgets;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Services\ApiUsageGuard;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $contacted = Lead::whereNotNull('contacted_at')->count();
        $replied = Lead::whereNotNull('replied_at')->count();

        return [
            Stat::make('Leads coletados', Lead::count()),
            Stat::make('Qualificados aguardando envio', Lead::where('status', LeadStatus::Qualificado)->count())
                ->description(Lead::where('status', LeadStatus::NaFila)->count().' na fila'),
            Stat::make('Enviados hoje', Lead::whereDate('contacted_at', today())->count().' / '.config('leads.outreach_daily_limit')),
            Stat::make('Taxa de resposta', $contacted ? round($replied / $contacted * 100).'%' : '—')
                ->description("{$replied} de {$contacted} contatados"),
            Stat::make('Places API no mês', app(ApiUsageGuard::class)->used('google_places').' / '.config('services.google_places.monthly_limit')),
        ];
    }
}
