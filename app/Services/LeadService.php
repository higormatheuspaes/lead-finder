<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Support\Collection;

class LeadService
{
    public function __construct(private LeadQualifier $qualifier) {}

    /** Coloca na fila de envio; só leads qualificados entram. Retorna quantos entraram. */
    public function queue(Collection $leads): int
    {
        $qualified = $leads->where('status', LeadStatus::Qualificado);
        $qualified->each->update(['status' => LeadStatus::NaFila]);

        return $qualified->count();
    }

    public function discard(Lead|Collection $leads, string $reason): void
    {
        Collection::wrap($leads)->each->update(['status' => LeadStatus::Descartado, 'discard_reason' => $reason]);
    }

    public function markConverted(Lead $lead): void
    {
        $lead->update(['status' => LeadStatus::Convertido]);
    }

    /** Roda as regras de novo; quem já entrou no funil de envio não muda. Retorna quantos foram requalificados. */
    public function requalify(Collection $leads): int
    {
        $requalifiable = $leads->filter(fn (Lead $lead) => in_array($lead->status, LeadStatus::REQUALIFIABLE, true));

        $requalifiable->each(function (Lead $lead) {
            $this->qualifier->apply($lead);
            $lead->save();
        });

        return $requalifiable->count();
    }

    public function requalifyAll(): int
    {
        $count = 0;
        Lead::whereIn('status', LeadStatus::REQUALIFIABLE)->chunkById(200, function ($leads) use (&$count) {
            $count += $this->requalify($leads);
        });

        return $count;
    }
}
