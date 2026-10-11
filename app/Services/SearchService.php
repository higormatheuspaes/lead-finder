<?php

namespace App\Services;

use App\Jobs\CollectLeadsJob;
use App\Models\Search;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Cria uma busca por bairro (ou uma só para a cidade, sem bairros) e manda para a fila.
     *
     * @param  string[]  $bairros
     * @return Collection<int, Search>
     */
    public function start(string $query, string $city, array $bairros = []): Collection
    {
        $bairros = array_filter(array_map('trim', $bairros));

        return collect($bairros ?: [null])->map(function (?string $bairro) use ($query, $city) {
            $search = Search::create([
                'query' => $query,
                'city' => $bairro ? "{$bairro}, {$city}" : $city,
                'status' => 'pending',
            ]);
            CollectLeadsJob::dispatch($search);

            return $search;
        });
    }
}
