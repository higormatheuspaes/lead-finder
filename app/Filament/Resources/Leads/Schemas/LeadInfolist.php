<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Negócio')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nome no Google'),
                        TextEntry::make('greeting_name')->label('Nome de saudação')->placeholder('—'),
                        TextEntry::make('niche')->label('Nicho')->badge(),
                        TextEntry::make('primary_type')->label('Tipo no Google')->placeholder('—'),
                        TextEntry::make('phone_raw')->label('Telefone')->placeholder('—'),
                        TextEntry::make('address')->label('Endereço')->placeholder('—'),
                        TextEntry::make('website')->label('Site')->url(fn ($state) => $state, true)->placeholder('—'),
                        TextEntry::make('website_type')->label('Tipo de site')->badge(),
                        TextEntry::make('rating')->label('Nota')->placeholder('—'),
                        TextEntry::make('ratings_count')->label('Avaliações'),
                    ]),
                Section::make('Qualificação')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status')->badge(),
                        TextEntry::make('score'),
                        TextEntry::make('problem')->label('Problema')->placeholder('—'),
                        TextEntry::make('discard_reason')->label('Motivo do descarte')->placeholder('—'),
                        TextEntry::make('contacted_at')->label('Contatado em')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('replied_at')->label('Respondeu em')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('notes')->label('Notas')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
