<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Enums\WebsiteType;
use App\Models\ApiUsage;
use App\Models\Lead;
use App\Models\OptOut;
use App\Models\Search;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CollectLeadsTest extends TestCase
{
    use RefreshDatabase;

    private function place(string $id, array $overrides = []): array
    {
        return array_merge([
            'id' => $id,
            'displayName' => ['text' => "Academia | {$id} Fitness | Blumenau/SC"],
            'internationalPhoneNumber' => '+55 47 99122-61'.substr('00'.crc32($id), -2),
            'businessStatus' => 'OPERATIONAL',
            'userRatingCount' => 80,
            'rating' => 4.7,
        ], $overrides);
    }

    public function test_collects_paginates_and_qualifies_leads(): void
    {
        OptOut::create(['phone_e164' => '5547988887777']);

        Http::fake([
            'places.googleapis.com/*' => Http::sequence()
                ->push(['places' => [
                    $this->place('semsite'),
                    $this->place('insta', ['websiteUri' => 'https://www.instagram.com/x']),
                    $this->place('comsite', ['websiteUri' => 'https://academia.com.br']),
                    $this->place('fixo', ['internationalPhoneNumber' => '+55 47 3322-1100']),
                    $this->place('fechado', ['businessStatus' => 'CLOSED_PERMANENTLY']),
                ], 'nextPageToken' => 'p2'])
                ->push(['places' => [
                    $this->place('optout', ['internationalPhoneNumber' => '+55 47 98888-7777']),
                    $this->place('semsite'), // repetido: não duplica
                ]]),
        ]);

        $this->artisan('leads:buscar', ['query' => 'academia', '--sync' => true])->assertSuccessful();

        $search = Search::sole();
        $this->assertSame('done', $search->status);
        $this->assertSame([2, 7, 6], [$search->pages_fetched, $search->results_count, $search->new_leads_count]);
        $this->assertSame(2, ApiUsage::sole()->requests);
        $this->assertSame('p2', Http::recorded()[1][0]['pageToken']);

        $lead = fn (string $id) => Lead::where('google_place_id', $id)->sole();

        $this->assertSame(LeadStatus::Qualificado, $lead('semsite')->status);
        $this->assertSame(65, $lead('semsite')->score); // 40 sem site + 15 avaliações + 10 nota
        $this->assertSame('semsite Fitness', $lead('semsite')->greeting_name);
        $this->assertSame('ainda não tem um site próprio', $lead('semsite')->problem);

        $this->assertSame(WebsiteType::Social, $lead('insta')->website_type);
        $this->assertSame('usa só o Instagram como site', $lead('insta')->problem);

        $this->assertSame('score_baixo', $lead('comsite')->discard_reason);
        $this->assertSame('telefone_fixo', $lead('fixo')->discard_reason);
        $this->assertSame('fechado', $lead('fechado')->discard_reason);
        $this->assertSame('opt_out', $lead('optout')->discard_reason);
    }

    public function test_drops_non_http_urls_from_google(): void
    {
        Http::fake(['places.googleapis.com/*' => ['places' => [
            $this->place('evil', ['websiteUri' => 'javascript:alert(1)', 'googleMapsUri' => 'https://maps.google.com/?cid=1']),
        ]]]);

        $this->artisan('leads:buscar', ['query' => 'academia', '--sync' => true])->assertSuccessful();

        $this->assertNull(Lead::sole()->website);
        $this->assertSame('https://maps.google.com/?cid=1', Lead::sole()->maps_url);
    }

    public function test_does_not_requalify_leads_already_in_outreach(): void
    {
        Lead::create(['google_place_id' => 'x', 'name' => 'X', 'niche' => 'academia', 'status' => LeadStatus::Enviado]);
        Http::fake(['places.googleapis.com/*' => ['places' => [$this->place('x', ['businessStatus' => 'CLOSED_TEMPORARILY'])]]]);

        $this->artisan('leads:buscar', ['query' => 'academia', '--sync' => true])->assertSuccessful();

        $this->assertSame(LeadStatus::Enviado, Lead::sole()->status);
        $this->assertSame('CLOSED_TEMPORARILY', Lead::sole()->business_status);
    }

    public function test_stops_at_monthly_limit_without_calling_google(): void
    {
        config(['services.google_places.monthly_limit' => 5]);
        ApiUsage::create(['provider' => 'google_places', 'period' => now()->format('Y-m'), 'requests' => 5]);
        Http::fake();

        $this->artisan('leads:buscar', ['query' => 'academia', '--sync' => true])->assertFailed();

        Http::assertNothingSent();
        $this->assertSame('failed', Search::sole()->status);
        $this->assertStringContainsString('Limite mensal', Search::sole()->error);
    }
}
