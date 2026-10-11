<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Filament\Resources\Searches\Pages\ManageSearches;
use App\Jobs\CollectLeadsJob;
use App\Models\Lead;
use App\Models\Search;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class PanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function lead(LeadStatus $status, array $attrs = []): Lead
    {
        return Lead::create(array_merge([
            'google_place_id' => uniqid(), 'name' => 'Academia X', 'niche' => 'academia', 'status' => $status,
        ], $attrs));
    }

    public function test_pages_render(): void
    {
        $lead = $this->lead(LeadStatus::Qualificado);

        $this->get('/admin')->assertOk();
        $this->get('/admin/leads')->assertOk();
        $this->get('/admin/searches')->assertOk();
        Livewire::test(ViewLead::class, ['record' => $lead->id])->assertOk();
    }

    public function test_nova_busca_creates_one_search_per_bairro(): void
    {
        Queue::fake();

        Livewire::test(ManageSearches::class)
            ->callAction('novaBusca', ['query' => 'academia', 'city' => 'Blumenau', 'bairros' => "Velha\n\nItoupava Norte\n"]);

        $this->assertSame(['Velha, Blumenau', 'Itoupava Norte, Blumenau'], Search::pluck('city')->all());
        Queue::assertPushed(CollectLeadsJob::class, 2);
    }

    public function test_bulk_queue_only_moves_qualified_leads(): void
    {
        $qualified = $this->lead(LeadStatus::Qualificado);
        $discarded = $this->lead(LeadStatus::Descartado);

        Livewire::test(ListLeads::class, ['activeTab' => 'todos'])
            ->callTableBulkAction('fila', [$qualified, $discarded]);

        $this->assertSame(LeadStatus::NaFila, $qualified->fresh()->status);
        $this->assertSame(LeadStatus::Descartado, $discarded->fresh()->status);
    }

    public function test_row_discard_asks_reason(): void
    {
        $lead = $this->lead(LeadStatus::Qualificado);

        Livewire::test(ListLeads::class)
            ->callTableAction('descartar', $lead, ['reason' => 'já é cliente']);

        $this->assertSame('já é cliente', $lead->fresh()->discard_reason);
    }
}
