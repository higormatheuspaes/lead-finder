<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadStatus;
use App\Enums\WebsiteType;
use App\Models\Lead;
use App\Services\LeadService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('score', 'desc')
            ->columns([
                TextColumn::make('name')->label('Negócio')->searchable()->wrap()
                    ->description(fn (Lead $record) => $record->address),
                TextInputColumn::make('greeting_name')->label('Saudação')->rules(['nullable', 'string', 'max:255']),
                TextColumn::make('niche')->label('Nicho')->badge()->toggleable(),
                TextColumn::make('phone_raw')->label('Telefone')->searchable(),
                TextColumn::make('website_type')->label('Site')->badge()
                    ->url(fn (Lead $record) => $record->website, true),
                TextColumn::make('ratings_count')->label('Avaliações')->sortable(),
                TextColumn::make('rating')->label('Nota')->sortable(),
                TextColumn::make('score')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('discard_reason')->label('Motivo')->toggleable(),
                TextColumn::make('contacted_at')->label('Contatado em')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(LeadStatus::class)->multiple(),
                SelectFilter::make('niche')->label('Nicho')
                    ->options(fn () => Lead::distinct()->orderBy('niche')->pluck('niche', 'niche')->all()),
                SelectFilter::make('website_type')->label('Tipo de site')->options(WebsiteType::class),
                SelectFilter::make('discard_reason')->label('Motivo de descarte')
                    ->options(fn () => Lead::whereNotNull('discard_reason')->distinct()->pluck('discard_reason', 'discard_reason')->all()),
                Filter::make('min_score')
                    ->schema([TextInput::make('min_score')->label('Score mínimo')->numeric()])
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['min_score'],
                        fn (Builder $query, $min) => $query->where('score', '>=', $min),
                    )),
                Filter::make('is_mobile')->label('Só celular')->toggle()
                    ->query(fn (Builder $query) => $query->where('is_mobile', true)),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->url(fn (Lead $record) => "https://wa.me/{$record->phone_e164}", true)
                    ->visible(fn (Lead $record) => $record->is_mobile),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('maps')
                        ->label('Abrir no Maps')
                        ->icon(Heroicon::OutlinedMapPin)
                        ->url(fn (Lead $record) => $record->maps_url, true)
                        ->visible(fn (Lead $record) => filled($record->maps_url)),
                    Action::make('convertido')
                        ->label('Marcar convertido')
                        ->icon(Heroicon::OutlinedTrophy)
                        ->color('success')
                        ->requiresConfirmation()
                        ->hidden(fn (Lead $record) => $record->status === LeadStatus::Convertido)
                        ->action(fn (Lead $record, LeadService $leads) => $leads->markConverted($record)),
                    Action::make('descartar')
                        ->label('Descartar')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->schema([TextInput::make('reason')->label('Motivo')->required()])
                        ->hidden(fn (Lead $record) => $record->status === LeadStatus::Descartado)
                        ->action(fn (Lead $record, array $data, LeadService $leads) => $leads->discard($record, $data['reason'])),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('fila')
                        ->label('Colocar na fila')
                        ->icon(Heroicon::OutlinedQueueList)
                        ->action(function (Collection $records, LeadService $leads) {
                            Notification::make()
                                ->title("{$leads->queue($records)} de {$records->count()} na fila")
                                ->body('Só leads qualificados entram na fila.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('descartar')
                        ->label('Descartar')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->schema([TextInput::make('reason')->label('Motivo')->required()])
                        ->action(fn (Collection $records, array $data, LeadService $leads) => $leads->discard($records, $data['reason']))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('requalificar')
                        ->label('Requalificar')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->requiresConfirmation()
                        ->modalDescription('Roda as regras de novo. Leads já na fila ou enviados não mudam.')
                        ->action(function (Collection $records, LeadService $leads) {
                            Notification::make()->title("{$leads->requalify($records)} leads requalificados")->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
