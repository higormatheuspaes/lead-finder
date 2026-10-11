<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadServiceTest extends TestCase
{
    use RefreshDatabase;

    private function lead(LeadStatus $status, array $attrs = []): Lead
    {
        return Lead::create(array_merge([
            'google_place_id' => uniqid(), 'name' => 'Academia X', 'niche' => 'academia', 'status' => $status,
        ], $attrs));
    }

    public function test_queue_only_moves_qualified_leads(): void
    {
        $qualified = $this->lead(LeadStatus::Qualificado);
        $discarded = $this->lead(LeadStatus::Descartado);

        $this->assertSame(1, app(LeadService::class)->queue(Lead::all()));

        $this->assertSame(LeadStatus::NaFila, $qualified->fresh()->status);
        $this->assertSame(LeadStatus::Descartado, $discarded->fresh()->status);
    }

    public function test_requalify_skips_leads_already_in_outreach(): void
    {
        $sent = $this->lead(LeadStatus::Enviado, ['business_status' => 'CLOSED_PERMANENTLY']);
        $new = $this->lead(LeadStatus::Novo, ['business_status' => 'CLOSED_PERMANENTLY']);

        $this->assertSame(1, app(LeadService::class)->requalifyAll());

        $this->assertSame(LeadStatus::Enviado, $sent->fresh()->status);
        $this->assertSame('fechado', $new->fresh()->discard_reason);
    }

    public function test_discard_accepts_one_lead_or_many(): void
    {
        $a = $this->lead(LeadStatus::Qualificado);
        $b = $this->lead(LeadStatus::Qualificado);

        app(LeadService::class)->discard($a, 'não gostei');
        app(LeadService::class)->discard(Lead::whereKey($b->id)->get(), 'repetido');

        $this->assertSame('não gostei', $a->fresh()->discard_reason);
        $this->assertSame([LeadStatus::Descartado, 'repetido'], [$b->fresh()->status, $b->fresh()->discard_reason]);
    }
}
