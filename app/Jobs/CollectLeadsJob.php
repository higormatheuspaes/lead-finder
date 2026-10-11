<?php

namespace App\Jobs;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Search;
use App\Services\LeadQualifier;
use App\Services\Places\PlacesClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class CollectLeadsJob implements ShouldQueue
{
    use Queueable;

    private const MAX_PAGES = 3; // limite do Google: 60 resultados por busca

    // abaixo do retry_after (90s) da fila database, senão outro worker pegaria o mesmo job
    public int $timeout = 75;

    // sem novas tentativas: cada tentativa gasta chamadas da cota
    public int $tries = 1;

    public function __construct(public Search $search) {}

    public function handle(PlacesClient $places, LeadQualifier $qualifier): void
    {
        $this->search->update(['status' => 'running', 'error' => null]);

        $query = "{$this->search->query} em {$this->search->city}";
        $token = null;
        $pages = $total = $new = 0;

        do {
            $data = $places->searchText($query, $token);
            $pages++;

            foreach ($data['places'] ?? [] as $place) {
                $total++;
                $lead = Lead::firstOrNew(['google_place_id' => $place['id']]);

                if (! $lead->exists) {
                    $new++;
                    $lead->search_id = $this->search->id;
                    $lead->niche = $this->search->query;
                }

                $lead->fill([
                    'name' => $place['displayName']['text'] ?? 'Sem nome',
                    'phone_raw' => $place['internationalPhoneNumber'] ?? null,
                    'website' => $place['websiteUri'] ?? null,
                    'rating' => $place['rating'] ?? null,
                    'ratings_count' => $place['userRatingCount'] ?? 0,
                    'address' => $place['formattedAddress'] ?? null,
                    'primary_type' => $place['primaryType'] ?? null,
                    'business_status' => $place['businessStatus'] ?? null,
                    'maps_url' => $place['googleMapsUri'] ?? null,
                ]);

                // só requalifica quem ainda não entrou no funil de envio
                if (in_array($lead->status ?? LeadStatus::Novo, LeadStatus::REQUALIFIABLE, true)) {
                    $qualifier->apply($lead);
                }

                $lead->save();
            }

            // grava a cada página: se a próxima falhar, os totais parciais ficam certos
            $this->search->update(['pages_fetched' => $pages, 'results_count' => $total, 'new_leads_count' => $new]);

            $token = $data['nextPageToken'] ?? null;
            if ($token && $pages < self::MAX_PAGES) {
                sleep(2); // o Google demora para liberar o token da próxima página
            }
        } while ($token && $pages < self::MAX_PAGES);

        $this->search->update(['status' => 'done']);
    }

    public function failed(?Throwable $e): void
    {
        $this->search->update(['status' => 'failed', 'error' => $e?->getMessage()]);
    }
}
