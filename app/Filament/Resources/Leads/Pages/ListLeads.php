<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use App\Services\LeadService;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    /** Chamado pelo clique no botão WhatsApp da tabela (o link abre no navegador, isto só registra o envio). */
    public function markLeadContacted(int $leadId, LeadService $leads): void
    {
        $leads->markContacted(Lead::findOrFail($leadId));
    }

    public function getTabs(): array
    {
        $tab = fn (string $label, array $statuses) => Tab::make($label)
            ->badge(Lead::whereIn('status', $statuses)->count())
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', $statuses));

        return [
            'qualificados' => $tab('Qualificados', [LeadStatus::Qualificado]),
            'fila' => $tab('Na fila', [LeadStatus::NaFila]),
            'enviados' => $tab('Enviados', [LeadStatus::Enviado, LeadStatus::Entregue, LeadStatus::Lido, LeadStatus::Falhou]),
            'responderam' => $tab('Responderam', [LeadStatus::Respondeu, LeadStatus::Convertido]),
            'descartados' => $tab('Descartados', [LeadStatus::Descartado, LeadStatus::OptOut]),
            'todos' => Tab::make('Todos'),
        ];
    }
}
