<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    // Só o que faz sentido editar à mão; o resto vem do Google.
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('greeting_name')
                    ->label('Nome de saudação')
                    ->helperText('Vai no "Olá, {nome}!" da mensagem do WhatsApp.')
                    ->maxLength(255),
                Textarea::make('notes')->label('Notas')->rows(5)->columnSpanFull(),
            ]);
    }
}
