<?php

namespace App\Filament\Resources\Searches\Pages;

use App\Filament\Resources\Searches\SearchResource;
use App\Services\ApiUsageGuard;
use App\Services\SearchService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageSearches extends ManageRecords
{
    protected static string $resource = SearchResource::class;

    public function getSubheading(): string
    {
        $used = app(ApiUsageGuard::class)->used('google_places');
        $limit = config('services.google_places.monthly_limit');

        return "Places API este mês: {$used} / {$limit} chamadas (cada busca gasta até 3).";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('novaBusca')
                ->label('Nova busca')
                ->icon(Heroicon::OutlinedPlus)
                ->schema([
                    TextInput::make('query')->label('Nicho')->placeholder('academia')->required(),
                    TextInput::make('city')->label('Cidade')->default('Blumenau')->required(),
                    Textarea::make('bairros')
                        ->label('Bairros (opcional, um por linha)')
                        ->helperText('Cada bairro vira uma busca separada, de até 60 resultados.')
                        ->rows(4),
                ])
                ->action(function (array $data, SearchService $searches) {
                    $created = $searches->start($data['query'], $data['city'], explode("\n", $data['bairros'] ?? ''));

                    Notification::make()->title("{$created->count()} busca(s) enviada(s) para a fila")->success()->send();
                }),
        ];
    }
}
