<?php

namespace App\Filament\Resources\Searches;

use App\Filament\Resources\Searches\Pages\ManageSearches;
use App\Models\Search;
use App\Services\SearchService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SearchResource extends Resource
{
    protected static ?string $model = Search::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $modelLabel = 'busca';

    protected static ?string $pluralModelLabel = 'buscas';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->poll('5s') // acompanha pending → running → done sem recarregar
            ->columns([
                TextColumn::make('query')->label('Nicho')->searchable(),
                TextColumn::make('city')->label('Cidade')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Na fila',
                        'running' => 'Rodando',
                        'done' => 'Concluída',
                        'failed' => 'Falhou',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'done' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn (Search $record) => $record->error),
                TextColumn::make('pages_fetched')->label('Páginas'),
                TextColumn::make('results_count')->label('Resultados'),
                TextColumn::make('new_leads_count')->label('Leads novos'),
                TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([
                Action::make('repetir')
                    ->label('Repetir')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->modalDescription('Gasta até 3 chamadas da cota mensal da Places API.')
                    ->action(function (Search $record, SearchService $searches) {
                        $searches->start($record->query, $record->city);
                        Notification::make()->title('Busca enviada para a fila')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSearches::route('/'),
        ];
    }
}
