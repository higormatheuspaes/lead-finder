<?php

namespace App\Console\Commands;

use App\Jobs\CollectLeadsJob;
use App\Models\Search;
use App\Services\SearchService;
use Illuminate\Console\Command;
use Throwable;

class BuscarLeads extends Command
{
    protected $signature = 'leads:buscar {query : Nicho, ex. academia} {--city=Blumenau} {--sync : Roda na hora, sem fila}';

    protected $description = 'Busca negócios na Places API e salva como leads';

    public function handle(SearchService $searches): int
    {
        if (! $this->option('sync')) {
            $search = $searches->start($this->argument('query'), $this->option('city'))->sole();
            $this->info("Busca #{$search->id} enviada para a fila.");

            return self::SUCCESS;
        }

        // --sync: roda o job aqui mesmo para ver o resultado no terminal
        $search = Search::create([
            'query' => $this->argument('query'),
            'city' => $this->option('city'),
            'status' => 'pending',
        ]);

        try {
            CollectLeadsJob::dispatchSync($search);
        } catch (Throwable) {
            // o failed() do job já gravou o erro na Search
        }

        $search->refresh();
        $this->info("Busca #{$search->id}: {$search->status} — {$search->pages_fetched} páginas, {$search->results_count} resultados, {$search->new_leads_count} leads novos.");

        if ($search->error) {
            $this->error($search->error);
        }

        return $search->status === 'done' ? self::SUCCESS : self::FAILURE;
    }
}
